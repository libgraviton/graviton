<?php
/**
 * test resource generator
 */

namespace Graviton\GeneratorBundle\Generator;

use Graviton;
use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * Test the ResourceGenerator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class ResourceGeneratorTest extends GravitonTestCase
{
    /**
     * Dir to put the generated files in
     *
     * @var string
     */
    const GRAVITON_TMP_DIR = "/tmp/generateDocumentTest";

    /**
     * (non-PHPdoc)
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     *
     * @return void
     */
    public function setUp()
    {
        // Make sure the temp dir exists
        $target = self::GRAVITON_TMP_DIR . "/Resources/config/";

        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }
    }

    /**
     * (non-PHPdoc)
     *
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
     * Verify addParam
     *
     * @return void
     */
    public function testAddParam()
    {
        $xml = '<container>' .
            '<parameters>' .
            '<parameter key="graviton.test.class">GravitonDyn\TestBundle\Test</parameter>' .
            '</parameters>' .
            '</container>';

        $dom = new \DOMDocument();
        $dom->loadXML('<container/>');
        $paramKey = 'graviton.test.class';
        $value = 'GravitonDyn\TestBundle\Test';

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy')
            ->disableOriginalConstructor()
            ->setMethods(array('addParam'))
            ->getProxy();

        $newDom = $generator->addParam($dom, $paramKey, $value);

        $this->assertXmlStringEqualsXmlString($xml, $newDom->saveXML());
    }

    /**
     * Verify addParam
     *
     * @return void
     */
    public function testAddParamDuplicateKey()
    {
        $xml = '<container>' .
            '<parameters>' .
            '<parameter key="graviton.test.class">GravitonDyn\TestBundle\Test</parameter>' .
            '</parameters>' .
            '</container>';

        $dom = new \DOMDocument();
        $dom->loadXML('<container/>');
        $paramKey = 'graviton.test.class';
        $value = 'GravitonDyn\TestBundle\Test';

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy')
            ->disableOriginalConstructor()
            ->setMethods(array('addParam'))
            ->getProxy();

        $dom = $generator->addParam($dom, $paramKey, $value);
        $dom = $generator->addParam($dom, $paramKey, $value);

        $this->assertXmlStringEqualsXmlString($xml, $dom->saveXML());
    }

    /**
     * Validates behavior of addRolesParameter
     *
     * @return void
     */
    public function testAddRolesParameter()
    {
        $xml = '<container>' .
            '<parameters>' .
            '<parameter key="graviton.test.roles"  type="collection">' .
            '<parameter>GRAVITON_USER</parameter>' .
            '<parameter>GRAVITON_ADMIN</parameter>' .
            '</parameter>' .
            '</parameters>' .
            '</container>';

        $dom = new \DOMDocument();
        $dom->loadXML('<container/>');
        $docName = 'graviton.test';
        $roles = array('GRAVITON_USER', 'GRAVITON_ADMIN');

        $jsonDefinitionMock = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->setMethods(array('getRoles'))
            ->getMock();
        $jsonDefinitionMock
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles);

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy')
            ->disableOriginalConstructor()
            ->setMethods(array('addRolesParameter'))
            ->getProxy();

        $generator->addRolesParameter($dom, $jsonDefinitionMock, $docName);
        $this->assertXmlStringEqualsXmlString($xml, $dom->saveXML());
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
        $servicesMock = $this->getMockBuilder('\DOMDocument')
            ->setMethods(array("saveXml"))
            ->getMock();

        $jsonDefinitionMock = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->getMock();

        $parameters = array(
            'base' => $base,
            'document' => 'DocumentTest',
            'bundle' => 'MyTestBundle',
            'json' => $jsonDefinitionMock
        );

        $documentNS = $parameters['base'] . 'Document\\' . $parameters['document'];
        $docName = 'graviton.bundlename.document.documenttest';

        $dir = self::GRAVITON_TMP_DIR;
        $document = 'DocumentTest';

        $generator = $this->getMockBuilder('\Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy')
            ->disableOriginalConstructor()
            ->setMethods(array('renderFile', 'loadServices', 'addParam', 'addService', 'addRolesParameter'))
            ->getMock();

        $generator
            ->expects($this->exactly(2))
            ->method('renderFile');

        $generator
            ->expects($this->once())
            ->method('loadServices')
            ->will($this->returnValue($servicesMock));

        $generator
            ->expects($this->once())
            ->method('addParam')
            ->with(
                $this->equalTo($servicesMock),
                $this->equalTo($docName . '.class'),
                $this->equalTo($documentNS)
            )
            ->will($this->returnValue($servicesMock));

        $generator
            ->expects($this->once())
            ->method('addRolesParameter')
            ->with(
                $this->equalTo($servicesMock),
                $this->equalTo($jsonDefinitionMock),
                $this->equalTo($docName)
            )
            ->will($this->returnValue($servicesMock));

        $generator
            ->expects($this->once())
            ->method('addService')
            ->with(
                $this->equalTo($servicesMock),
                $this->equalTo($docName)
            )
            ->will($this->returnValue($servicesMock));

        $generator->generateDocument($parameters, $dir, $document, false);
    }

    /**
     * Return the base names the test should cover
     *
     * @return array
     */
    public function baseNameProvider()
    {
        return array(
            array('Graviton\BundleNameBundle\\'),
            array('Graviton\BundleName\\'),
            array('Graviton\BundleNamebundle\\'),
        );
    }
}
