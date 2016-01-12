<?php
/**
 * HttpLoaderTest
 */

namespace Graviton\ProxyBundle\Tests\Definition\Loader;

use Graviton\ProxyBundle\Definition\Loader\HttpLoader;

/**
 * tests for the HttpLoader class
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class HttpLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpLoader
     */
    private $sut;

    /**
     * setup
     *
     * @return void
     */
    public function setup()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->setConstructorArgs([200])
            ->getMock();
        $response
            ->expects($this->any())
            ->method("getBody")
            ->willReturn("{ 'test': 'bablaba' }");
        $curlMock = $this->getMock('Guzzle\Common\Collection');
        $request = $this->getMockForAbstractClass('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->any())
            ->method("send")
            ->withAnyParameters()
            ->willReturn($response);
        $request->method('getCurlOptions')
            ->willReturn($curlMock);
        $client = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client
            ->expects($this->any())
            ->method('get')
            ->willReturn($request);
        $validator = $this->getMockForAbstractClass('Symfony\Component\Validator\Validator\ValidatorInterface');

        $this->sut = new HttpLoader($validator, $client);
    }

    /**
     * test the support method
     *
     * @return void
     */
    public function testSupports()
    {
        $client = $this->getMockBuilder('Guzzle\Http\Client')->getMock();
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')
            ->getMockForAbstractClass();
        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(array());

        $sut = new HttpLoader($validator, $client);
        $this->assertTrue($sut->supports("test/test.json"));

        $validatorFail = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')
            ->getMockForAbstractClass();
        $validatorFail
            ->expects($this->once())
            ->method('validate')
            ->willReturn(array("error text"));

        $sut = new HttpLoader($validatorFail, $client);
        $this->assertFalse($sut->supports("test/again.json"));
    }

    /**
     * load method return null
     *
     * @return HttpLoader
     */
    public function testLoadReturnNull()
    {
        $url = "http://localhost/test.json";
        $this->assertNull($this->sut->load($url));

        $mock = $this->getMockBuilder('Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy')
            ->disableOriginalConstructor()
            ->setMethods(['supports'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method("supports")
            ->willReturn(false);

        $this->sut->setDispersalStrategy($mock);
        $this->assertNull($this->sut->load($url));
    }

    /**
     * test the load method
     *
     * @depends testLoadReturnNull
     *
     * @return void
     */
    public function testLoad()
    {
        $apiDefinition = $this->getMockBuilder('Graviton\ProxyBundle\Definition\ApiDefinition')->getMock();

        $mock = $this->getMockBuilder('Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy')
            ->disableOriginalConstructor()
            ->setMethods(['supports', 'process'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method("supports")
            ->willReturn(true);
        $mock
            ->expects($this->once())
            ->method("process")
            ->willReturn($apiDefinition);

        $this->sut->setDispersalStrategy($mock);
        $loadedContent = $this->sut->load("http://localhost/test/url/blub");
        $this->assertInstanceOf('Graviton\ProxyBundle\Definition\ApiDefinition', $loadedContent);
    }

    /**
     * test a load with cached content
     *
     * @return void
     */
    public function testLoadWithCache()
    {
        $storeKey = 'testSwagger';
        $cachedContent = '{"swagger": "2.0"}';
        $apiDefinition = $this->getMock('Graviton\ProxyBundle\Definition\ApiDefinition');

        $mock = $this->getMockBuilder('Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy')
            ->disableOriginalConstructor()
            ->setMethods(['supports', 'process'])
            ->getMock();
        $mock ->expects($this->once())
            ->method("supports")
            ->willReturn(true);
        $mock->expects($this->once())
            ->method("process")
            ->with($this->equalTo($cachedContent))
            ->willReturn($apiDefinition);
        $this->sut->setDispersalStrategy($mock);

        $cacheMock = $this->getMockBuilder('Doctrine\Common\Cache\FilesystemCache')
            ->disableOriginalConstructor()
            ->setMethods(['contains', 'fetch'])
            ->getMock();
        $cacheMock->expects($this->once())
            ->method('contains')
            ->with($this->equalTo($storeKey))
            ->will($this->returnValue(true));
        $cacheMock->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($storeKey))
            ->willReturn($cachedContent);
        $this->sut->setCache($cacheMock, 'ProxyBundle', 1234);
        $this->sut->setOptions(['prefix' => $storeKey]);

        $content = $this->sut->load("http://localhost/test/blablabla");
        $this->assertInstanceOf('Graviton\ProxyBundle\Definition\ApiDefinition', $content);
    }
}
