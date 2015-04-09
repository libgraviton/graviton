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

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
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

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
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
     * @dataProvider collectionParamsProvider
     *
     * @param string $xml    expected result
     * @param array  $params set of parameters
     *
     * @return void
     */
    public function testAddCollectionParam($xml, array $params)
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<container/>');
        $key = 'graviton.test.roles';

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('addCollectionParam'))
            ->getProxy();

        $generator->addCollectionParam($dom, $key, $params);
        $this->assertXmlStringEqualsXmlString($xml, $dom->saveXML());
    }

    /**
     * Provides a set of test cases
     *
     * @return array
     */
    public function collectionParamsProvider()
    {
        return array(
            'muliple, no key' => array(
                '<container><parameters><parameter key="graviton.test.roles"  type="collection">' .
                '<parameter>GRAVITON_USER</parameter><parameter>GRAVITON_ADMIN</parameter></parameter>' .
                '</parameters></container>',
                array('GRAVITON_USER', 'GRAVITON_ADMIN')
            ),
            'muliple, with key' => array(
                '<container><parameters><parameter key="graviton.test.roles"  type="collection">' .
                '<parameter key="user">GRAVITON_USER</parameter><parameter key="admin">GRAVITON_ADMIN</parameter>' .
                '</parameter></parameters></container>',
                array('user' => 'GRAVITON_USER', 'admin' => 'GRAVITON_ADMIN')
            ),
        );
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

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('loadServices'))
            ->getProxy();

        $services = $generator->loadServices(self::GRAVITON_TMP_DIR);

        $this->assertInstanceOf('\DomDocument', $services);
        $this->assertSame($services, $generator->loadServices(self::GRAVITON_TMP_DIR));
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

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('addXmlParameter'))
            ->setProperties(array('xmlParameters'))
            ->getProxy();

        $generator->xmlParameters = new ArrayCollection();

        $generator->addXmlParameter($value, $key, $type);

        $this->assertTrue($generator->xmlParameters->contains($element));
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


        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generateParameters', 'loadServices'))
            ->setProperties(array('xmlParameters'))
            ->getProxy();

        $generator->xmlParameters = new ArrayCollection($parameters);

        $generator->generateParameters(self::GRAVITON_TMP_DIR);

        $services = $generator->loadServices(self::GRAVITON_TMP_DIR);

        $this->assertXmlStringEqualsXmlString($xml, $services->saveXML());
    }

    /**
     * @return void
     */
    public function testExtractTargetRelations()
    {
        $relations = array(
            'app' => (object) array(
                'type' => 'embed',
                'collectionName' => 'ModuleApp',
                'localProperty' => 'app',
                'localValueField' => 'app',
                'path' => '/core/app/'
            ),
            'mode' => (object) array(
                'type' => 'embed',
                'collectionName' => 'ModuleApp',
                'localProperty' => 'mode',
                'localValueField' => 'mode',
                'path' => '/core/mode/'
            ),
        );
        $prefix = 'graviton.test';
        $expected = array(
            'content' => array(
                'app' => '/core/app/',
                'mode' => '/core/mode/',
            ),
            'key' => $prefix . '.moduleapp.relations' ,
            'type' => 'collection',
        );

        $generator = $this->getProxyBuilder('\Graviton\GeneratorBundle\Generator\ResourceGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('extractTargetRelations'))
            ->setProperties(array('xmlParameters'))
            ->getProxy();

        $generator->xmlParameters = new ArrayCollection();
        $generator->extractTargetRelations($relations, $prefix);

        $this->assertTrue($generator->xmlParameters->contains($expected));
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

        $docName = 'graviton.bundlename.document.documenttest';

        $dir = self::GRAVITON_TMP_DIR;
        $document = 'DocumentTest';

        $generator = $this->getMockBuilder('\Graviton\GeneratorBundle\Tests\Generator\ResourceGeneratorProxy')
            ->disableOriginalConstructor()
            ->setMethods(array('renderFile', 'loadServices', 'addXmlParameter', 'addService'))
            ->getMock();

        $generator
            ->expects($this->exactly(2))
            ->method('renderFile');

        $generator
            ->expects($this->exactly(2))
            ->method('loadServices')
            ->will($this->returnValue($servicesMock));

        $generator
            ->expects($this->exactly(2))
            ->method('addXmlParameter');


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
