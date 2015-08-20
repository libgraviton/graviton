<?php
/**
 * functional test for /hans/showcase
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for /hans/showcase
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ShowcaseControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/hans/showcase/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/hans/showcase/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * checks empty objects
     *
     * @return void
     */
    public function testGetEmptyObject()
    {
        $showCase = (object) [
            'anotherInt'            => 100,
            'aBoolean'              => true,
            'testField'             => ['en' => 'test'],
            'someOtherField'        => ['en' => 'other'],
            'contactCode'           => [
                'someDate'          => '2015-06-07T06:30:00+0000',
                'text'              => ['en' => 'text'],
            ],
            'contact'               => [
                'type'      => 'type',
                'value'     => 'value',
                'protocol'  => 'protocol',
                'uri'       => 'protocol:value',
            ],

            'nestedApps'            => [],
            'unstructuredObject'    => (object) [],
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase/', $showCase);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertNull($client->getResults());

        $url = $client->getResponse()->headers->get('Location');
        $this->assertNotNull($url);

        $client = static::createRestClient();
        $client->request('GET', $url);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $created = $client->getResults();
        $this->assertEquals($showCase->nestedApps, $created->nestedApps);
        $this->assertEquals($showCase->unstructuredObject, $created->unstructuredObject);
    }

    /**
     * see how our missing fields are explained to us
     *
     * @return void
     */
    public function testMissingFields()
    {
        $document = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-incomplete.json'),
            true
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $expectedErrors = [];
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "children[someOtherField]";
        $expectedError->message = "This value is not valid.";
        $expectedErrors[] = $expectedError;
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.contact.type";
        $expectedError->message = "This value should not be blank.";
        $expectedErrors[] = $expectedError;
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.contact.protocol";
        $expectedError->message = "This value should not be blank.";
        $expectedErrors[] = $expectedError;
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.contact.value";
        $expectedError->message = "This value should not be blank.";
        $expectedErrors[] = $expectedError;

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
        );
    }

    /**
     * insert various formats to see if all works as expected
     *
     * @dataProvider postCreationDataProvider
     *
     * @param string $filename filename
     *
     * @return void
     */
    public function testPost($filename)
    {
        // showcase contains some datetime fields that we need rendered as UTC in the case of this test
        ini_set('date.timezone', 'UTC');
        $document = json_decode(
            file_get_contents($filename),
            false
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase/', $document);
        $response = $client->getResponse();

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));

        $result = $client->getResults();

        // unset id as we cannot compare and don't care
        $this->assertNotNull($result->id);
        unset($result->id);

        $this->assertJsonStringEqualsJsonString(
            json_encode($document),
            json_encode($result)
        );
    }

    /**
     * Provides test sets for the testPost() test.
     *
     * @return array
     */
    public function postCreationDataProvider()
    {
        $basePath = dirname(__FILE__).'/../resources/';

        return array(
            'minimal' => array($basePath.'showcase-minimal.json'),
            'complete' => array($basePath.'showcase-complete.json')
        );
    }

    /**
     * are extra fields denied?
     *
     * @return void
     */
    public function testExtraFieldPost()
    {
        ini_set('date.timezone', 'UTC');
        $document = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-minimal.json'),
            false
        );
        $document->extraFields = "nice field";

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $expectedErrors = [];
        $expectedErrors[0] = new \stdClass();
        $expectedErrors[0]->propertyPath = "";
        $expectedErrors[0]->message = "This form should not contain extra fields.";

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
        );
    }

    /**
     * Test RQL select statement
     *
     * @return void
     */
    public function testRqlSelect()
    {
        $this->loadFixtures(
            ['GravitonDyn\ShowCaseBundle\DataFixtures\MongoDB\LoadShowCaseData'],
            null,
            'doctrine_mongodb'
        );

        $filtred = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-rql-select-filtred.json'),
            false
        );

        $fields = [
            'someFloatyDouble',
            'contact.uri',
            'contactCode.text.en',
            'unstructuredObject.booleanField',
            'unstructuredObject.hashField.someField',
            'unstructuredObject.nestedArrayField.anotherField',
            'nestedCustomers.$ref'
        ];
        $rqlSelect = 'select('.implode(',', array_map([$this, 'encodeRqlString'], $fields)).')';

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/?'.$rqlSelect);
        $this->assertEquals($filtred, $client->getResults());
    }

    /**
     * Encode string value in RQL
     *
     * @param string $value String value
     * @return string
     */
    private function encodeRqlString($value)
    {
        return strtr(
            rawurlencode($value),
            [
                '-' => '%2D',
                '_' => '%5F',
                '.' => '%2E',
                '~' => '%7E',
            ]
        );
    }
}
