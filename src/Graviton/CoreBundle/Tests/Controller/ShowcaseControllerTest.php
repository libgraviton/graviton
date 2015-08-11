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
     * see how our missing fields are explained to us
     *
     * @return void
     */
    public function testMissingFields()
    {
        $document = [
            'anotherInt'  => 6555488894525,
            'testField'   => ['en' => 'a test string'],
            'contactCode' => [
                'text'     => ['en' => 'Some Text'],
                'someDate' => '1984-05-01T00:00:00+0000',
            ],
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'children[someOtherField]',
                    'message'       => 'This value is not valid.',
                ],
            ],
            $client->getResults()
        );
    }

    /**
     * see how our empty fields are explained to us
     *
     * @return void
     */
    public function testEmptyAllFields()
    {
        $document = [
            'anotherInt'  => 6555488894525,
            'testField'   => ['en' => 'a test string'],
            'contactCode' => [
                'text'     => ['en' => 'Some Text'],
                'someDate' => '1984-05-01T00:00:00+0000',
            ],
            'contact'     => [
                'type'      => '',
                'value'     => '',
                'protocol'  => '',
            ],
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'children[someOtherField]',
                    'message'       => 'This value is not valid.',
                ],
            ],
            $client->getResults()
        );
    }

    /**
     * see how our empty fields are explained to us
     *
     * @return void
     */
    public function testEmptyFields()
    {
        $document = [
            'anotherInt'  => 6555488894525,
            'testField'   => ['en' => 'a test string'],
            'contactCode' => [
                'text'     => ['en' => 'Some Text'],
                'someDate' => '1984-05-01T00:00:00+0000',
            ],
            'contact'     => [
                'type'      => 'abc',
                'value'     => '',
                'protocol'  => '',
            ],
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'children[someOtherField]',
                    'message'       => 'This value is not valid.',
                ],
                (object) [
                    'propertyPath'  => 'data.contact.protocol',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.contact.value',
                    'message'       => 'This value should not be blank.',
                ],
            ],
            $client->getResults()
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
        $client->post('/hans/showcase', $document);
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

        $initial = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-rql-select-initial.json'),
            false
        );
        $filtred = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-rql-select-filtred.json'),
            false
        );

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase');
        $this->assertEquals($initial, $client->getResults());

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
        $client->request('GET', '/hans/showcase?q='.$rqlSelect);
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
