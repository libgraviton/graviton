<?php
/**
 * test resource generator
 */

namespace Graviton\GeneratorBundle\Generator;

use Graviton;

/**
 * Test the ResourceGenerator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class ResourceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dir to put the generated files in
     *
     * @var string
     */
    const GRAVITON_TMP_DIR = "/tmp/generateDocumentTest";

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     *
     * @return void
     */
    public function setUp()
    {
        // Make sure the temp dir exists
        $target = self::GRAVITON_TMP_DIR."/Resources/config/";

        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     *
     * @return void
     */
    public function tearDown()
    {
        $target = self::GRAVITON_TMP_DIR;

        if (!is_dir($target)) {
            rmdir($target);
        }
    }

    /**
     * Test the generateDocument method (without repository creation)
     *
     * @dataProvider baseNameProvider
     *
     * @param string $base Basepath
     *
     * @return void
     */
    public function testGenerateDocument($base)
    {
        $servicesMock = $this->getMockBuilder("\DOMDocument")
            ->setMethods(array("saveXml"))
            ->getMock();

        $parameters = array(
            "base" => $base,
            "document" => "DocumentTest",
            "bundle" => "MyTestBundle"
        );

        $documentNS = $parameters['base'] . 'Document\\' . $parameters['document'];
        $docName = "graviton.bundlename.document.documenttest";

        $dir = self::GRAVITON_TMP_DIR;
        $document = "DocumentTest";

        $generator = $this->getMockBuilder("Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy")
            ->disableOriginalConstructor()
            ->setMethods(array("renderFile", "loadServices", "addParam", "addService"))
            ->getMock();

        $generator
            ->expects($this->exactly(2))
            ->method("renderFile");

        $generator
            ->expects($this->once())
            ->method("loadServices")
            ->will($this->returnValue($servicesMock));

        $generator
             ->expects($this->once())
             ->method("addParam")
             ->with(
                 $this->equalTo($servicesMock),
                 $this->equalTo($docName. ".class"),
                 $this->equalTo($documentNS)
             )
             ->will($this->returnValue($servicesMock));

        $generator
            ->expects($this->once())
            ->method("addService")
            ->with(
                $this->equalTo($servicesMock),
                $this->equalTo($docName)
            )
            ->will($this->returnValue($servicesMock));

        $generator->generateDocument($parameters, $dir, $document, false);
    }

    /**
     * Return the basenames the test should cover
     *
     * @return multitype:multitype:string
     */
    public function baseNameProvider()
    {
        return array(
            array("Graviton\\BundleNameBundle\\"),
            array("Graviton\\BundleName\\"),
            array("Graviton\\BundleNamebundle\\"),
        );
    }

    /**
     * test the mapField method used in generate
     *
     * @dataProvider mapFieldProvider
     *
     * @param array $field  input field
     * @param array $expect expected outcome
     *
     * @return void
     */
    public function testMapField($field, $expect)
    {
        $sut = $this->getSimpleGenerator();
        $this->assertEquals($expect, $sut->mapField($field));
    }

    /**
     * fields and what their expected to map to
     *
     * @return array[]
     */
    public function mapFieldProvider()
    {
        return array (
            array(
                array('type' => 'object', 'fieldName' => 'names'),
                array('type' => 'object', 'fieldName' => 'names', 'serializerType' => 'array', 'singularName' => 'name')
            ),
            array(
                array('type' => 'string[]', 'fieldName' => 'arrayOfStrings'),
                array(
                    'type' => 'string[]',
                    'fieldName' => 'arrayOfStrings',
                    'serializerType' => 'array<string>',
                    'singularName' => 'arrayOfString'
                )
            ),
            array(
                array('type' => 'array', 'fieldName' => 'hackyArray'),
                array(
                    'type' => 'array',
                    'fieldName' => 'hackyArray',
                    'serializerType' => 'array<string>',
                    'singularName' => 'hackyArray'
                )
            ),
        );
    }

    /**
     * test data from JsonDefinition
     *
     * @dataProvider mapFieldWithJsonDataProvider
     *
     * @param array $field    input field
     * @param array $json     json data mocked as array
     * @param array $expected expected outcome
     *
     * @return void
     */
    public function testMapFieldWithJsonData($field, $json, $expected)
    {
        $jsonDef = $this
            ->getMockBuilder('Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->setMethods(array('getField'))
            ->getMock();
        $fieldDef = $this
            ->getMockBuilder('Graviton\GeneratorBundle\Definition\DefinitionElementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldDef
            ->expects($this->once())
            ->method('getDefAsArray')
            ->will($this->returnValue($json));
        $jsonDef
            ->expects($this->atLeastOnce())
            ->method('getField')
            ->will($this->onConsecutiveCalls($fieldDef, $fieldDef, $fieldDef));

        $sut = $this->getSimpleGenerator();

        $sut->setJson($jsonDef);
        $this->assertEquals($expected, $sut->mapField($field));
    }

    /**
     * @return array
     */
    public function mapFieldWithJsonDataProvider()
    {
        return array(
            array(
               array('type' => 'string', 'fieldName' => 'tests'),
               array('doctrineType' => 'integer', 'fieldName' => 'foo'),
               array(
                   'type' => 'integer',
                   'serializerType' => 'string',
                   'singularName' => 'test',
                   'fieldName' => 'foo',
                   'doctrineType' => 'integer'
               ),
            ),
        );
    }

    /**
     * @return ResourceGenerator
     */
    protected function getSimpleGenerator()
    {
        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');
        $doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        return new ResourceGenerator($filesystem, $doctrine, $kernel);
    }
}
