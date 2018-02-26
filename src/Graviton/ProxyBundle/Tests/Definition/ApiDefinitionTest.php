<?php
/**
 * ApiDefinitionTest
 */

namespace Graviton\ProxyBundle\Tests\Definition;

use Graviton\ProxyBundle\Definition\ApiDefinition;

/**
 * test api definition
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class ApiDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test add endpoint
     *
     * @return void
     */
    public function testAddEndpoints()
    {
        $host = "localhost:8000";
        $basePath = "/v2/default/path";
        $endpoints = ["/user", "/group", "/user/uploadImage"];

        $sut = new ApiDefinition();
        $sut->setBasePath($basePath);
        $sut->setHost($host);
        foreach ($endpoints as $endpoint) {
            $sut->addEndpoint($endpoint);
        }

        $apiEndpoints = $sut->getEndpoints(false, null);
        $apiEndpointsWithoutBase = $sut->getEndpoints(false, null, '', false);

        $this->assertEquals($host, $sut->getHost());
        $this->assertCount(3, $apiEndpoints);
        foreach ($endpoints as $id => $endpoint) {
            $this->assertTrue($sut->hasEndpoint($endpoint));
            $this->assertEquals($basePath.$endpoint, $apiEndpoints[$id]);
            $this->assertEquals($endpoint, $apiEndpointsWithoutBase[$id]);
        }
    }

    /**
     * test endpoint definition without base path,but with host
     *
     * @return void
     */
    public function testGetEntpointWithoutPath()
    {
        $endpoint = "/this/is/an/endpoint";
        $prefix = "/testapi";
        $host = "blabla.talk:8080";

        $sut = new ApiDefinition();
        $sut->addEndpoint($endpoint);

        $this->assertEquals($endpoint, $sut->getEndpoints(false)[0]);
        $this->assertEquals($endpoint, $sut->getEndpoints(true)[0]);

        $sut->setHost($host);
        $this->assertEquals($endpoint, $sut->getEndpoints(false)[0]);
        $this->assertEquals($host.$endpoint, $sut->getEndpoints(true)[0]);
        $this->assertEquals($prefix.$endpoint, $sut->getEndpoints(false, $prefix)[0]);
        $this->assertEquals($host.$prefix.$endpoint, $sut->getEndpoints(true, $prefix)[0]);
    }

    /**
     * test endpoint definition with a configured host
     *
     * @return void
     */
    public function testGetEndPointsWithDefinedHost()
    {
        $endpoint = "/this/is/an/endpoint";
        $host = "blabla.talk:8080";
        $preferedHost = "someHost.talk:8000";

        $sut = new ApiDefinition();
        $sut->addEndpoint($endpoint);
        $sut->setHost($host);

        $this->assertEquals($preferedHost.$endpoint, $sut->getEndpoints(true, null, $preferedHost)[0]);
    }

    /**
     * test schema
     *
     * @return void
     */
    public function testAddSchema()
    {
        $endpoint = "test/schema/endpoint";
        $testschema = new \stdClass();
        $testschema->name = "test123";
        $testschema->description = "This is a description";

        $sut = new ApiDefinition();
        $sut->addSchema($endpoint, $testschema);
        $schema = $sut->getSchema($endpoint);

        $this->assertInstanceOf("\stdClass", $schema);
        $this->assertEquals($testschema->name, $schema->name);
        $this->assertEquals($testschema->description, $schema->description);
        $this->assertInstanceOf("\stdClass", $sut->getSchema("blablabla/endpoint"));
    }
}
