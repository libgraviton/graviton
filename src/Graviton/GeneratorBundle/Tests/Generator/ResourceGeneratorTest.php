<?php

namespace Graviton\GeneratorBundle\Generator;

use Graviton;

/**
 * Test the ResourceGenerator
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
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
}
