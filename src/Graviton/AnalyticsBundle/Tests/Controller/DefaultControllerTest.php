<?php
/**
 * Test cases for basic coverage for Analytics Bundle
 */
namespace Graviton\AnalyticsBundle\Tests\Controller;

use Symfony\Component\Routing\Router;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for Analytics
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefaultControllerTest extends RestTestCase
{
    /** @var Router */
    private $router;

    /**
     * Initial setup
     * @return void
     */
    public function setUp()
    {
        $this->router = $this->getContainer()->get('router');

        $this->loadFixtures(
            array(
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslatableData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslatablesApp',
                'GravitonDyn\CustomerBundle\DataFixtures\MongoDB\LoadCustomerData',
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Testing basic functionality
     * @return void
     */
    public function testIndex()
    {
        $client = static::createClient();

        // Let's get information from the schema
        $client->request('GET', '/analytics/schema/app');
        $content = $client->getResponse()->getContent();
        $schema = json_decode($content);

        // Check schema
        $sampleSchema = json_decode(
            '{
                    "title": "Application usage",
                    "description": "Data use for application access",
                    "type": "object",
                    "representation": "pie",
                    "properties": {
                      "id": {
                        "title": "ID",
                        "description": "Unique identifier",
                        "type": "string"
                      },
                      "count": {
                        "title": "count",
                        "description": "Sum of result",
                        "type": "integer"
                      }
                    }
                  }'
        );
        $this->assertEquals($sampleSchema, $schema);

        // Let's get information from the count
        $client->request('GET', '/analytics/app');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode('{"_id":"app-count","count":2}');
        $this->assertEquals($sampleData, $data);
    }

    /**
     * Testing basic functionality
     * @return void
     */
    public function testApp2Index()
    {
        $client = static::createClient();

        // Let's get information from the count
        $client->request('GET', '/analytics/app2');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode('{"_id":"app-count-2","count":1}');
        $this->assertEquals($sampleData, $data);
    }

    /**
     * Testing basic functionality
     * @return void
     */
    public function testCustomerCreateDateFilteringIndex()
    {
        $client = static::createClient();

        // Let's get information from the count
        $client->request('GET', '/analytics/customer-created-by-date');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode(
            '[
              {
                "_id": "100",
                "customerNumber": 1100,
                "name": "Acme Corps.",
                "created_year": 2014,
                "created_month": 7
              }
            ]'
        );
        $this->assertEquals($sampleData, $data);

        // Let's get information from the count, but cached version
        $client->request('GET', '/analytics/customer-created-by-date');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode(
            '[
              {
                "_id": "100",
                "customerNumber": 1100,
                "name": "Acme Corps.",
                "created_year": 2014,
                "created_month": 7
              }
            ]'
        );
        $this->assertEquals($sampleData, $data);
    }
}
