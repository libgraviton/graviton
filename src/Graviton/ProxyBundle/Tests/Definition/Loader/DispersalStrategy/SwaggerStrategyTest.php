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
     * @inheritDoc
     *
     * @return void
     */
    protected function setUp()
    {
        $swaggerParserMock = $this->getMockBuilder('\Swagger\Document')
            ->disableOriginalConstructor()
            ->setMethods(['setDocument'])
            ->getMock();
        $this->sut = new SwaggerStrategy($swaggerParserMock);
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
     * @param mixed $result  result
     * @param mixed $swagger swagger
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
            ->setMethods(['getBasePath', 'setDocument', 'getOperationsById', 'getSchemaResolver'])
            ->getMock();

        $statusCode = 200;
        $responseSchema = $swagger->paths->$orderPath->get->responses->$statusCode->schema;
        $orderDefinition = $swagger->definitions->Order;
        $userDefinition = $swagger->definitions->User;

        $responseSchemaMock = $this->getMockBuilder('\Swagger\Object\AbstractObject')
            ->disableOriginalConstructor()
            ->setMethods(['getDocument'])
            ->getMockForAbstractClass();
        $responseSchemaMock->expects($this->any())
            ->method('getDocument')
            ->will($this->onConsecutiveCalls($responseSchema, $orderDefinition, $userDefinition));

        $responseMock = $this->getMockBuilder('\Swagger\Object\Response')
            ->disableOriginalConstructor()
            ->setMethods(['getSchema'])
            ->getMock();
        $responseMock->expects($this->once())
            ->method('getSchema')
            ->willReturn($responseSchemaMock);

        $responsesMock = $this->getMockBuilder('\Swagger\Object\Responses')
            ->disableOriginalConstructor()
            ->setMethods(['getHttpStatusCode'])
            ->getMock();
        $responsesMock->expects($this->once())
            ->method('getHttpStatusCode')
            ->with($this->equalTo($statusCode))
            ->willReturn($responseMock);


        $operationMock = $this->getMockBuilder('\Swagger\Object\Operation')
            ->disableOriginalConstructor()
            ->setMethods(['getDocumentObjectProperty', 'getResponses'])
            ->getMock();
        $operationMock->expects($this->once())
            ->method('getResponses')
            ->willReturn($responsesMock);


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

        $schemaResolverMock = $this->getMockBuilder('\Swagger\SchemaResolver')
            ->disableOriginalConstructor()
            ->setMethods(['resolveReference'])
            ->getMock();
        $schemaResolverMock->expects($this->exactly(1))
            ->method('resolveReference')
            ->withAnyParameters()
            ->willReturn($responseSchemaMock);

        $swaggerParserMock
            ->expects($this->once())
            ->method('getOperationsById')
            ->willReturn($operations);
        $swaggerParserMock
            ->expects($this->exactly(1))
            ->method('getSchemaResolver')
            ->willReturn($schemaResolverMock);

        $this->sut = new SwaggerStrategy($swaggerParserMock);

        $apiDefinition = $this->sut->process($content, array('host' => 'localhost'));
        $this->assertInstanceOf('Graviton\ProxyBundle\Definition\ApiDefinition', $apiDefinition);
        $this->assertCount(2, $apiDefinition->getEndpoints());
    }
}
