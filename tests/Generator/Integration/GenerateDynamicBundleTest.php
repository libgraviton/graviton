<?php
/**
 * integration test that covers the result of our generator
 */

namespace Graviton\Tests\Generator\Integration;

use Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand;
use Graviton\GeneratorBundle\Event\GenerateSchemaEvent;
use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Generator\SchemaGenerator;
use Graviton\GeneratorBundle\RuntimeDefinition\RuntimeDefinitionBuilder;
use Graviton\GeneratorBundle\Schema\SchemaBuilder;
use Graviton\Tests\Generator\Utils;
use Graviton\RestBundle\Service\I18nUtils;
use Graviton\Tests\GravitonTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
            ->getMock();

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

        $intUtils = new I18nUtils('en', 'en,de,fr');

        $bundleGenerator = new BundleGenerator();
        $bundleGenerator->setExposeSyntheticMap(null);

        $schemaBuilder = new SchemaBuilder();

        $intUtils = new I18nUtils('de', 'en,de,fr,it');

        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturn(new GenerateSchemaEvent());

        $schemaGenerator = new SchemaGenerator();
        $schemaGenerator->setSchemaBuilder($schemaBuilder);
        $schemaGenerator->setIntUtils($intUtils);
        $schemaGenerator->setEventDispatcher($eventDispatcher);

        $resourceGenerator = new ResourceGenerator(
            new Filesystem(),
            $intUtils,
            $fieldMapper,
            new ResourceGenerator\ParameterBuilder(),
            $schemaGenerator
        );
        $resourceGenerator->setExposeSyntheticMap(null);

        $dynamicBundleGenerator = new DynamicBundleBundleGenerator();
        $dynamicBundleGenerator->setExposeSyntheticMap(null);

        $runtimeDefinitionBuilder = new RuntimeDefinitionBuilder();

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
            $schemaGenerator,
            $runtimeDefinitionBuilder
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
