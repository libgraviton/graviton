<?php
/**
 * test resource generator
 */

namespace Graviton\GeneratorBundle\Generator;

use Doctrine\Common\Collections\ArrayCollection;
use Graviton;
use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * Test the ResourceGenerator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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

        $generator = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator');
        $protectedMethod = $this->getPrivateClassMethod($generator, 'addParam');

        $newDom = $protectedMethod->invokeArgs($generator, [$dom, $paramKey, $value]);

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

        $generator = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator');
        $protectedMethod = $this->getPrivateClassMethod($generator, 'addParam');

        $dom = $protectedMethod->invokeArgs($generator, [$dom, $paramKey, $value]);
        $dom = $protectedMethod->invokeArgs($generator, [$dom, $paramKey, $value]);

        $this->assertXmlStringEqualsXmlString($xml, $dom->saveXML());
    }

    /**
     * Validates behavior of addRolesParameter
     *
     * @return void
     */
    public function testAddCollectionParam()
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
        $key = 'graviton.test.roles';
        $roles = array('GRAVITON_USER', 'GRAVITON_ADMIN');

        $generator = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator');
        $protectedMethod = $this->getPrivateClassMethod($generator, 'addCollectionParam');

        $protectedMethod->invokeArgs($generator, [$dom, $key, $roles]);
        $this->assertXmlStringEqualsXmlString($xml, $dom->saveXML());
    }

    /**
     * @return void
     */
    public function testLoadServices()
    {
        // generate dummy services.xml
        file_put_contents(
            self::GRAVITON_TMP_DIR . '/Resources/config/services.xml',
            '<?xml version="1.0"?><container/>'
        );

        $generator = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator');
        $protectedMethod = $this->getPrivateClassMethod($generator, 'loadServices');

        $services = $protectedMethod->invokeArgs($generator, [self::GRAVITON_TMP_DIR]);

        $this->assertInstanceOf('\DomDocument', $services);
        $this->assertSame($services, $protectedMethod->invokeArgs($generator, [self::GRAVITON_TMP_DIR]));
    }

    /**
     * @return void
     */
    public function testAddXmlParameter()
    {
        $value = 'the fox jumps over the lazy dog.';
        $key = 'some_document.class';
        $type = 'string';

        $element = array(
            'content' => $value,
            'key' => $key,
            'type' => strtolower($type),
        );

        $generator = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator');
        $protectedMethod = $this->getPrivateClassMethod($generator, 'addXmlParameter');
        $protectedProp = $this->getPrivateClassProperty($generator, 'xmlParameters');

        $protectedProp->setValue($generator, new ArrayCollection());
        $protectedMethod->invokeArgs($generator, [$value, $key, $type]);

        $this->assertTrue($protectedProp->getValue($generator)->contains($element));
    }

    /**
     * @return void
     */
    public function testGenerateParameters()
    {
        // generate dummy services.xml
        file_put_contents(
            self::GRAVITON_TMP_DIR . '/Resources/config/services.xml',
            '<?xml version="1.0"?><container/>'
        );

        $xml = '<container>' .
            '<parameters>' .
            '<parameter key="graviton.test.parameter">some ext</parameter>' .
            '<parameter key="graviton.test.collection" type="collection">' .
            '<parameter>item1</parameter>' .
            '<parameter>item2</parameter>' .
            '</parameter>' .
            '</parameters>' .
            '</container>';

        $parameters = array(
            array(
                'content' => 'some ext',
                'key' => 'graviton.test.parameter',
                'type' => 'string',
            ),
            array(
                'content' => array('item1', 'item2'),
                'key' => 'graviton.test.collection',
                'type' => 'collection',
            ),
        );

        $generator = $this->createMock('\Graviton\GeneratorBundle\Generator\ResourceGenerator');
        $protectedGenParameters = $this->getPrivateClassMethod($generator, 'generateParameters');
        $protectedLoadServices = $this->getPrivateClassMethod($generator, 'loadServices');
        $protectedProp = $this->getPrivateClassProperty($generator, 'xmlParameters');

        $protectedProp->setValue($generator, new ArrayCollection($parameters));
        $protectedGenParameters->invokeArgs($generator, [self::GRAVITON_TMP_DIR]);
        $services = $protectedLoadServices->invokeArgs($generator, [self::GRAVITON_TMP_DIR]);

        $this->assertXmlStringEqualsXmlString($xml, $services->saveXML());
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
            ->setMethods(['saveXml', 'getElementsByTagName'])
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

        $dir = self::GRAVITON_TMP_DIR;
        $document = 'DocumentTest';

        $generator = $this->getMockBuilder('\Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy')
            ->disableOriginalConstructor()
            ->setMethods(array('renderFile', 'loadServices', 'addXmlParameter', 'addService'))
            ->getMock();

        $generator
            ->expects($this->exactly(7))
            ->method('renderFile');

        $generator
            ->expects($this->exactly(2))
            ->method('loadServices')
            ->will($this->returnValue($servicesMock));

        $generator
            ->expects($this->exactly(2))
            ->method('addXmlParameter');

        $containerNodeMock = $this->getMockBuilder('\DOMNode')
            ->setMethods(['item', 'appendChild'])
            ->getMock();

        $servicesMock
            ->expects($this->any())
            ->method('getElementsByTagName')
            ->willReturn($containerNodeMock);

        $containerNodeMock->length = 0;
        $containerNodeMock
            ->expects($this->exactly(2))
            ->method('item')
            ->with(0)
            ->willReturn($containerNodeMock);

        $generator
            ->expects($this->exactly(3))
            ->method('addService')
            ->with(
                $this->equalTo($servicesMock)//,
                //$this->equalTo($docName)
            )
            ->will($this->returnValue($servicesMock));

        $generator->generateDocument($parameters, $dir, $document);
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
