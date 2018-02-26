<?php
/**
 * test a JsonExceptionLister
 */

namespace Graviton\CoreBundle\Tests\Services;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Functional test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonExceptionListerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\TestCaseReadOnlyBundle\DataFixtures\MongoDB\LoadTestCaseReadOnlyData',
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test status header KO
     *
     * @return void
     */
    public function testStatus200()
    {
        $client = static::createRestClient();
        $client->request('GET', "/testcase/readonly/");

        /** @var Response $response */
        $response = $client->getResponse();
        
        $this->assertEquals($response::HTTP_OK, $response->getStatusCode());
      
    }

    /**
     * Test status header KO
     * todo Resolve a correct DocumentManager but with wrong connection params
     *
     * @return void
     */
    public function testStatus500()
    {
        /** @var DocumentManager $documentManager */
        $documentManager = $this->getContainer()->get('doctrine_mongodb.odm.faulty_document_manager');

        $client = static::createRestClient();
        $client->getContainer()->set('doctrine_mongodb.odm.default_document_manager', $documentManager);
        $client->request('GET', "/testcase/readonly/");

        $response = $client->getResponse();
        $content = $response->getContent();
        $json = json_decode($content, true);
        $this->assertEquals($response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode(), 'Content is: '.$content);
        $this->assertArrayHasKey("code", $json, 'Content is: '.$content);
        $this->assertArrayHasKey("message", $json, 'Content is: '.$content);

    }



}
