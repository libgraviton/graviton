<?php
/**
 * integration test that covers the result of our generator
 */

namespace Graviton\GeneratorBundle\Test\Integration;

use Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand;
use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Tests\Utils;
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
            ->setMethods(['load'])
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
        $fieldMapper->addMapper(new ResourceGenerator\FieldJsonMapper());
        $fieldMapper->addMapper(new ResourceGenerator\FieldTitleMapper());

        $command = new GenerateDynamicBundleCommand(
            $loaderDouble,
            new BundleGenerator(),
            new ResourceGenerator(
                new Filesystem(),
                $fieldMapper,
                new ResourceGenerator\ParameterBuilder()
            ),
            new DynamicBundleBundleGenerator(),
            Utils::getSerializerInstance()
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

        // check if all needed file exist
        $fileList = file(__DIR__.'/Resources/generated_filelist');
        foreach ($fileList as $file) {
            $file = $generationDir.trim($file);
            $this->assertTrue((is_dir($file) || is_file($file)));
        }

        // expose as
        $serializerConf = $this->getSerializerFile(
            $generationDir.'GravitonDyn/TestBBundle/Resources/config/serializer/Document.TestB.xml'
        );
        $this->assertSame('$c', (string) $serializerConf->class[0]->property[4]['serialized-name']);

        // doctrine stuff
        $doctrineConf = $this->getYmlFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/doctrine/TestA.mongodb.yml'
        );
        // * mapped superclass definition
        $this->assertSame('mappedSuperclass', $doctrineConf['GravitonDyn\TestABundle\Document\TestABase']['type']);
        // * basic definition
        $this->assertSame('document', $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['type']);
        $this->assertSame('TestA', $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['collection']);
        $this->assertSame(
            'COLLECTION_PER_CLASS',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['inheritanceType']
        );
        // * relations
        $this->assertSame(
            'GravitonDyn\TestABundle\Document\TestAEmbedEmbedded',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['embedOne']['embed']['targetDocument']
        );
        $this->assertSame(
            'GravitonDyn\TestBBundle\Document\TestBEmbedded',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['embedOne']['testEmbed']['targetDocument']
        );
        $this->assertSame(
            'GravitonDyn\TestABundle\Document\TestAExtrefEmbedded',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['embedOne']['extref']['targetDocument']
        );
        $this->assertSame(
            'GravitonDyn\TestBBundle\Document\TestB',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['referenceOne']['testbRef']['targetDocument']
        );
        $this->assertSame(
            'all',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestA']['referenceOne']['testbRef']['cascade']
        );

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
        // * embed is excluded as well
        $serializerConf = $this->getSerializerFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/serializer/Document.TestAExtref.xml'
        );
        $this->assertTrue((boolean) $serializerConf->class[0]->property[0]['exclude']);

        // extref
        $doctrineConf = $this->getYmlFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/doctrine/TestAExtref.mongodb.yml'
        );
        $this->assertSame(
            'extref',
            $doctrineConf['GravitonDyn\TestABundle\Document\TestAExtref']['fields']['ref']['type']
        );
        // * expose as for extref
        $serializerConf = $this->getSerializerFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/serializer/Document.TestAExtref.xml'
        );
        $this->assertSame(
            '$ref',
            (string) $serializerConf->class[0]->property[3]['serialized-name']
        );
        $this->assertSame(
            'Graviton\DocumentBundle\Entity\ExtReference',
            (string) $serializerConf->class[0]->property[3]->type
        );

        // services
        $serviceConf = $this->getYmlFile(
            $generationDir.'GravitonDyn/TestABundle/Resources/config/services.yml'
        );
        // * repository is defined
        $this->assertTrue(isset($serviceConf['services']['gravitondyn.testa.repository.testaembed']));
        // * points to doctrine factory
        $this->assertSame(
            ['@doctrine_mongodb.odm.default_document_manager', 'getRepository'],
            $serviceConf['services']['gravitondyn.testa.repository.testaembed']['factory']
        );
        // * tag and router base is set
        $this->assertSame(
            ['name' => 'graviton.rest', 'collection' => 'TestA', 'router-base' => '/testa'],
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
