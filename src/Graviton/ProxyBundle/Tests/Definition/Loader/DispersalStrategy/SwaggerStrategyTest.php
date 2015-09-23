<?php
/**
 * SwaggerStrategyTest
 */

namespace Graviton\ProxyBundle\Tests\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy;

/**
 * tests for the SwaggerStrategy class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
        $this->sut = new SwaggerStrategy();

        $this->swagger = new \stdClass();
        $this->swagger->swagger = "2.0";
        $this->swagger->paths = new \stdClass();
        $this->swagger->definitions = new \stdClass();
        $this->swagger->info = new \stdClass();
        $this->swagger->info->title = "test swagger";
        $this->swagger->info->version = "1.0.0";
        $this->swagger->basePath = "/api/prefix";
        $this->swagger->host = "testapi.local";
    }


    /**
     * test the supports method
     *
     * @return void
     */
    public function testSupported()
    {
        $this->assertTrue($this->sut->supports(json_encode($this->swagger)));
    }

    /**
     * test the supports method, when JSON not supported
     *
     * @return void
     */
    public function testNotSupported()
    {
        $notValidJson = clone $this->swagger;
        unset($notValidJson->paths);
        unset($notValidJson->info->title);
        $this->assertFalse($this->sut->supports(json_encode($notValidJson)));
    }

    /**
     * test missing fallback data
     *
     * @expectedException RuntimeException
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
        $this->testProcessMethod(0);

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

        $apiDefinition = $this->testProcessMethod(2);
        foreach ($apiDefinition->getEndpoints(false) as $endpoint) {
            $this->assertEquals($person, $apiDefinition->getSchema($endpoint));
        }
    }

    /**
     * test a delete endpoint
     *
     * @return void
     */
    public function testProcessDeleteEndpoint()
    {
        $fallbackData = array('host' => 'localhost');
        $deleteEndpoint = array();
        $deleteEndpoint['delete'] = new \stdClass();
        $path = '/delete/endpoint';
        $this->swagger->paths->$path = (object) $deleteEndpoint;
        $this->testProcessMethod(1);
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
        $this->testProcessMethod(1);
    }

    /**
     * test process method
     *
     * @param int $count number of endpoints
     *
     * @return ApiDefinition
     */
    private function testProcessMethod($count)
    {
        $fallbackData = array('host' => 'localhost');
        $apiDefinition = $this->sut->process(json_encode($this->swagger), $fallbackData);
        $this->assertInstanceOf('Graviton\ProxyBundle\Definition\ApiDefinition', $apiDefinition);
        $this->assertCount($count, $apiDefinition->getEndpoints());

        return $apiDefinition;
    }
}
