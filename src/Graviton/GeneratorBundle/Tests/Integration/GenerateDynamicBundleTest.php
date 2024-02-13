<?php
/**
 * integration test that covers the result of our generator
 */

namespace Graviton\GeneratorBundle\Tests\Integration;

use Graviton\DocumentBundle\Annotation\ClassScanner;
use Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand;
use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Generator\SchemaGenerator;
use Graviton\GeneratorBundle\Tests\Utils;
use Graviton\SchemaBundle\Constraint\ConstraintBuilder;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GenerateDynamicBundleTest extends GravitonTestCase
{

    /**
     * generate and assert what comes out
     *
     * @return void
     */
    public function testGeneration()
    {
        $loaderDouble = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\Loader\\LoaderInterface')
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMockForAbstractClass();

        $definitions = [
            Utils::getJsonDefinition(__DIR__ . '/Resources/Definition/TestA.json'),
            Utils::getJsonDefinition(__DIR__ . '/Resources/Definition/TestB.json')
        ];

        $loaderDouble
            ->expects($this->once())
            ->method('load')
            ->willReturn($definitions);

        $fieldMapper = new ResourceGenerator\FieldMapper();
        $fieldMapper->addMapper(new ResourceGenerator\FieldTypeMapper());
        $fieldMapper->addMapper(new ResourceGenerator\FieldNameMapper());
        $fieldMapper->addMapper(new ResourceGenerator\FieldTitleMapper());
        $fieldMapper->addMapper(new ResourceGenerator\FieldHiddenRestrictionMapper());

        $bundleGenerator = new BundleGenerator();
        $bundleGenerator->setExposeSyntheticMap(null);

        $constraintBuilder = new ConstraintBuilder();

        $schemaGenerator = new SchemaGenerator();
        $schemaGenerator->setVersionInformation(['self' => 'testing']);
        $schemaGenerator->setConstraintBuilder($constraintBuilder);

        $resourceGenerator = new ResourceGenerator(
            new Filesystem(),
            $fieldMapper,
            new ResourceGenerator\ParameterBuilder(),
            $schemaGenerator
        );
        $resourceGenerator->setExposeSyntheticMap(null);

        $dynamicBundleGenerator = new DynamicBundleBundleGenerator();
        $dynamicBundleGenerator->setExposeSyntheticMap(null);

        $command = new GenerateDynamicBundleCommand(
            $loaderDouble,
            $bundleGenerator,
            $resourceGenerator,
            $dynamicBundleGenerator,
            Utils::getSerializerInstance(),
            null,
            null,
            null,
            null,
            null,
            schemaGenerator: $schemaGenerator
        );

        $application = new Application();
        $application->add($command);
        $fs = new Filesystem();

        $command = $application->find('graviton:generate:dynamicbundles');

        $tester = new CommandTester($command);
        $generationDir = sys_get_temp_dir().'/grvTest_'.uniqid().'/';
        $fs->mkdir($generationDir);

        $tester->execute(
            array_merge(
                ['command' => $command->getName()],
                ['--json' => __DIR__ . '/Resources/Definition/'],
                ['--srcDir' => $generationDir]
            )
        );

        /****** ASSERTS AFTER GENERATION *****/

        // expose as
        $serializerConf = $this->getSerializerFile(
            $generationDir.'GravitonDyn/TestBBundle/Resources/config/serializer/Document.TestB.xml'
        );
        $this->assertSame('$c', (string) $serializerConf->class[0]->property[4]['serialized-name']);

        // id not set (noIdDefined)
        // * normal one is not excluded
        $serializerConf = $this->getSerializerFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/serializer/Document.TestA.xml'
        );
        $this->assertNull($serializerConf->class[0]->property[0]['exclude']);
        // * no id defined is excluded (in TestB we don't have an id property in field definition)
        $serializerConf = $this->getSerializerFile(
            $generationDir.'GravitonDyn/TestBBundle/Resources/config/serializer/Document.TestB.xml'
        );
        $this->assertTrue((boolean) $serializerConf->class[0]->property[0]['exclude']);

        // services
        $serviceConf = $this->getYmlFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/services.yml'
        );
        // * repository is defined
        $this->assertTrue(isset($serviceConf['services']['gravitondyn.testa.repository.testa']));
        // * points to doctrine factory
        $this->assertSame(
            ['@doctrine_mongodb.odm.default_document_manager', 'getRepository'],
            $serviceConf['services']['gravitondyn.testa.repository.testa']['factory']
        );
        // * tag and router base is set
        $this->assertSame(
            ['name' => 'graviton.rest', 'collection' => 'TestA', 'router-base' => '/testa/'],
            $serviceConf['services']['gravitondyn.testa.controller.testa']['tags'][0]
        );

        // delete dir
        $fs->remove($generationDir);
    }

    /**
     * returns a serializer file
     *
     * @param string $file filename
     *
     * @return \SimpleXMLElement xml
     */
    private function getSerializerFile($file)
    {
        return new \SimpleXMLElement(file_get_contents($file));
    }

    /**
     * returns array from yml file
     *
     * @param string $file filename
     *
     * @return array structure
     */
    private function getYmlFile($file)
    {
        return Yaml::parseFile($file);
    }
}
