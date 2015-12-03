<?php
/**
 * SwaggerStrategyTest
 */

namespace Graviton\ProxyBundle\Tests\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy;

/**
 * tests for the SwaggerStrategy class
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class SwaggerStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwaggerStrategy
     */
    private $sut;

    /**
     * @var /stdClass
     */
    private $swagger;

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function setUp()
    {
        /*$swaggerParserMock = $this
            ->getMockBuilder('Swagger\Document')
            ->disableOriginalConstructor()
            ->getMock();*/


        /*$this->swagger = new \stdClass();
        $this->swagger->swagger = "2.0";
        $this->swagger->paths = new \stdClass();
        $this->swagger->definitions = new \stdClass();
        $this->swagger->info = new \stdClass();
        $this->swagger->info->title = "test swagger";
        $this->swagger->info->version = "1.0.0";
        $this->swagger->basePath = "/api/prefix";
        $this->swagger->host = "testapi.local";*/
    }

    /**
     * Provides data sets for testSupported()
     *
     * @return array
     */
    public function swaggerJsonDataProvider()
    {
        $basePath = dirname(__FILE__).'/../../../resources/';

        return array(
            array(false, file_get_contents($basePath.'not-supported-swagger.json')),
            array(true, file_get_contents($basePath.'simple-swagger.json')),
        );
    }

    /**
     * test the supports method
     *
     * @dataProvider swaggerJsonDataProvider
     *
     * @return void
     */
    public function testSupported($result, $swagger)
    {
        $this->assertEquals($result, $this->sut->supports($swagger));
    }

    /**
     * test missing fallback data
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Missing mandatory key (host) in fallback data set.
     *
     * @return void
     */
    public function testMissingFallbackData()
    {
        $this->sut->process('{}', array());
    }

    /**
     * processing swagger
     *
     * @return void
     */
    public function testProcessSwagger()
    {
        $content = file_get_contents(dirname(__FILE__).'/../../../resources/simple-swagger.json');
        $swagger = json_decode($content);
        $orderPath = '/order/{orderId}';

        $swaggerParserMock = $this->getMockBuilder('\Swagger\Document')
            ->disableOriginalConstructor()
            ->setMethods(['getBasePath', 'setDocument', 'getOperationsById'])
            ->getMock();

        $response = $swagger->paths->$orderPath->get->responses;

        $responseMock = $this->getMockBuilder('\Swagger\Object\Responses')
            ->disableOriginalConstructor()
            ->setMethods(['getHttpStatusCode'])
            ->getMock();
        $responseMock->expects($this->once())
            ->method('getHttpStatusCode')



        $operationMock = $this->getMockBuilder('\Swagger\Object\Operation')
            ->disableOriginalConstructor()
            ->setMethods(['getDocumentObjectProperty', 'getResponses'])
            ->getMock();
        $operationMock->expects($this->once())
            ->method('getDocumentObjectProperty');
        $operationMock->expects($this->once())
            ->method('getResponses')
            ->willReturn($responseMock);


        $serviceMock = $this->getMockBuilder('\Swagger\OperationReference')
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getMethod', 'getOperation'])
            ->getMock();
        $serviceMock->expects($this->exactly(3))
            ->method('getPath')
            ->will($this->onConsecutiveCalls('/user', '/user', '/order/{orderId}'));
        $serviceMock->expects($this->exactly(5))
            ->method('getMethod')
            ->will($this->onConsecutiveCalls('POST', 'DELETE', 'GET', 'POST', 'GET'));
        $serviceMock->expects($this->exactly(2))
            ->method('getOperation')
            ->willReturn($operationMock);


        $operations = [$serviceMock, $serviceMock, $serviceMock];

        $swaggerParserMock
            ->expects($this->once())
            ->method('getOperationsById')
            ->willReturn($operations);
        $this->sut = new SwaggerStrategy($swaggerParserMock);

        $this->sut->process($content, array('host' => 'localhost'));
        /*$this->callProcessMethod(0);

        $schema = array();
        $schema['$ref'] = '#/definitions/Person';

        $customer = array();
        $otherParam = array('in' => 'blub');
        $customer['post']['parameters'][] = $otherParam;
        $customer['get']['responses']['200']['schema'] = $schema;
        $customerPath = '/person/customer';
        $this->swagger->paths->$customerPath = (object) $customer;

        $bodyParam = array('in' => 'body', 'schema' => $schema);
        $consultant = array();
        $consultant['get']['responses']['400'] = new \stdClass();
        $consultant['post']['parameters'][] = $bodyParam;
        $consultantPath = '/person/consultant';
        $consultantPathWithId = '/person/consultant/{id}';
        $this->swagger->paths->$consultantPath = (object) $consultant;
        $this->swagger->paths->$consultantPathWithId = (object) $consultant;

        $person = new \stdClass();
        $person->type = "object";
        $person->properties = new \stdClass();
        $person->properties->id = new \stdClass();
        $person->properties->name = new \stdClass();
        $this->swagger->definitions->Person = $person;

        $apiDefinition = $this->callProcessMethod(2);
        foreach ($apiDefinition->getEndpoints(false) as $endpoint) {
            $this->assertEquals($person, $apiDefinition->getSchema($endpoint));
        }*/
    }

    /**
     * test a delete endpoint
     *
     * @return void
     */
    public function testProcessDeleteEndpoint()
    {
        $deleteEndpoint = array();
        $deleteEndpoint['delete'] = new \stdClass();
        $path = '/delete/endpoint';
        $this->swagger->paths->$path = (object) $deleteEndpoint;
        $this->callProcessMethod(1);
    }

    /**
     * test endpoint with no schema
     *
     * @return void
     */
    public function testProcessNoSchema()
    {
        $emptyEndpoint = array();
        $emptyEndpoint['get']['responses']['200']['schema'] = null;
        $path = '/no/schema/endpoint';
        $this->swagger->paths->$path = (object) $emptyEndpoint;
        $this->callProcessMethod(1);
    }

    /**
     * test process method
     *
     * @param int $count number of endpoints
     *
     * @return ApiDefinition
     */
    private function callProcessMethod($count)
    {
        $fallbackData = array('host' => 'localhost');
        $apiDefinition = $this->sut->process(json_encode($this->swagger), $fallbackData);
        $this->assertInstanceOf('Graviton\ProxyBundle\Definition\ApiDefinition', $apiDefinition);
        $this->assertCount($count, $apiDefinition->getEndpoints());

        return $apiDefinition;
    }
}
