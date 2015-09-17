<?php
/**
 * Created by PhpStorm.
 * User: samuel
 * Date: 15.09.15
 * Time: 17:05
 */

namespace Graviton\ProxyBundle\Tests\Definition\Loader;

use Graviton\ProxyBundle\Definition\Loader\HttpLoader;

class HttpLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpLoader
     */
    private $sut;

    /**
     * setup
     *
     * return void
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
        $request = $this->getMockForAbstractClass('Guzzle\Http\Message\RequestInterface');
        $request
            ->expects($this->any())
            ->method("send")
            ->withAnyParameters()
            ->willReturn($response);
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
        $this->sut->setDispersalStrategy(null);
        $this->assertNull($this->sut->load($url));

        $mock = $this->getMockBuilder('Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy')
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
     * @return void
     */
    public function testLoad()
    {
        $host = "http://localhost/";
        $endpoint = "test/endpoint";
        $apiDefinition = $this->getMockBuilder('Graviton\ProxyBundle\Definition\ApiDefinition')->getMock();

        $mock = $this->getMockBuilder('Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy')
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
}
