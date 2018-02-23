<?php
/**
 * TranslatableArrayControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData;
use Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData;
use Graviton\TestBundle\Client;
use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseTranslatableArrayBundle\Document\TestCaseTranslatableArray;
use GravitonDyn\TestCaseTranslatableArrayBundle\DataFixtures\MongoDB\LoadTestCaseTranslatableArrayData;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatableArrayControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists(TestCaseTranslatableArray::class)) {
            $this->markTestSkipped(sprintf('%s definition is not loaded', TestCaseTranslatableArray::class));
        }

        $this->loadFixtures(
            [
                LoadLanguageData::class,
                LoadMultiLanguageData::class,
                LoadTestCaseTranslatableArrayData::class,
            ],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test item schema
     *
     * @return void
     */
    public function testItemSchema()
    {
        $client = static::createRestClient();
        $this->getRequest($client, '/schema/testcase/translatable-array/item');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $schema = $client->getResults();
        $this->assertEquals('object', $schema->type);
        $this->assertItemSchema($schema);
    }

    /**
     * Test collection schema
     *
     * @return void
     */
    public function testCollectionSchema()
    {
        $client = static::createRestClient();
        $this->getRequest($client, '/schema/testcase/translatable-array/collection');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $schema = $client->getResults();
        $this->assertEquals('array', $schema->type);
        $this->assertEquals('object', $schema->items->type);
        $this->assertItemSchema($schema->items);
    }

    /**
     * Test GET one method
     *
     * @return void
     */
    public function testCheckGetOne()
    {
        $client = static::createRestClient();
        $this->getRequest($client, '/testcase/translatable-array/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($client->getResults());

        $this->assertFixtureData($client->getResults());
    }

    /**
     * Test GET all method
     *
     * @return void
     */
    public function testCheckGetAll()
    {
        $client = static::createRestClient();
        $this->getRequest($client, '/testcase/translatable-array/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $client->getResults());

        $this->assertFixtureData($client->getResults()[0]);
    }

    /**
     * Test POST method
     *
     * @return void
     */
    public function testPostMethod()
    {
        $data = $this->getPostData();

        $client = static::createRestClient();
        $client->post('/testcase/translatable-array/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $this->getRequest($client, $location, ['en', 'de']);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $result = $client->getResults();
        $this->assertNotNull($result->id);
        unset($result->id);
        $this->assertEquals($data, $result);
    }

    /**
     * Test PUT method
     *
     * @return void
     */
    public function testPutMethod()
    {
        $data = $this->getPostData();

        $client = static::createRestClient();
        $client->put('/testcase/translatable-array/testdata', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $client = static::createRestClient();
        $this->getRequest($client, '/testcase/translatable-array/testdata', ['en', 'de']);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $result = $client->getResults();
        $this->assertNotNull($result->id);
        unset($result->id);
        $this->assertEquals($data, $result);
    }

    /**
     * Test validation
     *
     * @return void
     */
    public function testValidation()
    {
        $data = (object) [
            'id'    => 'testdata',
            'field' => (object) [
                'de' => 'No "en" translation',
            ],
            'array' => [
                'Invalid value',
                (object) ['Invalid' => 'value'],
            ],
            'deep'  => (object) [
                'deep' => [
                    (object) [
                        'field' => 'Invalid value',
                        'array' => 'Invalid value',
                    ],
                    (object) [
                        'field' => (object) [
                            'en' => 'Valid value',
                        ],
                        'array' => [
                            (object) [],
                            (object) [
                                'en' => 'Valid value',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $client = static::createRestClient();
        $client->put('/testcase/translatable-array/testdata', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath' => 'field.en',
                    'message'      => 'The property en is required'
                ],
                (object) [
                    'propertyPath' => 'array[0]',
                    'message'      => 'String value found, but an object is required',
                ],
                (object) [
                    'propertyPath' => 'array[1].en',
                    'message'      => 'The property en is required',
                ],

                (object) [
                    'propertyPath' => 'deep.deep[0].field',
                    'message'      => 'String value found, but an object or a null is required',
                ],
                (object) [
                    'propertyPath' => 'deep.deep[0].array',
                    'message'      => 'String value found, but an array is required',
                ],

                (object) [
                    'propertyPath' => 'deep.deep[1].array[0].en',
                    'message'      => 'The property en is required',
                ],
            ],
            $client->getResults()
        );
    }

    /**
     * Assert fixture data
     *
     * @param object $data Fixture data
     * @return void
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    private function assertFixtureData($data)
    {
        $this->assertEquals(
            (object) [
                'id'    => 'testdata',
                'field' => (object) [
                    'en' => 'EN-1',
                ],
                'array' => [
                    (object) [
                        'en' => 'EN-2',
                    ],
                    (object) [
                        'en' => 'EN-3',
                    ],
                ],
                'deep'  => (object) [
                    'deep' => [
                        (object) [
                            'field' => (object) [
                                'en' => 'EN-4',
                            ],
                            'array' => [
                                (object) [
                                    'en' => 'EN-5',
                                ],
                                (object) [
                                    'en' => 'EN-6',
                                ],
                            ],
                        ],
                        (object) [
                            'field' => (object) [
                                'en' => 'EN-7',
                            ],
                            'array' => [
                                (object) [
                                    'en' => 'EN-8',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $data
        );
    }

    /**
     * Assert item schema
     *
     * @param object $schema Item schema
     * @return void
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    private function assertItemSchema($schema)
    {
        foreach ([
                     $schema->properties,
                     $schema->properties->deep->properties->deep->items->properties,
                 ] as $schema) {
            $this->assertEquals(['object', 'null'], $schema->field->type);
            $this->assertEquals('string', $schema->field->properties->de->type);
            $this->assertEquals('string', $schema->field->properties->en->type);
            $this->assertEquals('string', $schema->field->properties->fr->type);

            $this->assertEquals('array', $schema->array->type);
            $this->assertEquals('object', $schema->array->items->type);
            $this->assertEquals('string', $schema->array->items->properties->de->type);
            $this->assertEquals('string', $schema->array->items->properties->en->type);
            $this->assertEquals('string', $schema->array->items->properties->fr->type);
        }
    }

    /**
     * Make a get request
     *
     * @param Client $client    HTTP client
     * @param string $url       URL
     * @param array  $languages Languages
     * @return void
     */
    private function getRequest(Client $client, $url, array $languages = ['en'])
    {
        $client->request('GET', $url, [], [], ['HTTP_ACCEPT_LANGUAGE' => implode(',', $languages)]);
    }

    /**
     * Get post JSON
     *
     * @return string
     */
    private function getPostData()
    {
        return (object) [
            'field' => (object) [
                'en' => 'EN-10',
                'de' => 'DE-10',
            ],
            'array' => [
                (object) [
                    'en' => 'EN-20',
                    'de' => 'DE-20',
                ],
            ],
            'deep'  => (object) [
                'deep' => [
                    (object) [
                        'field' => (object) [
                            'en' => 'EN-30',
                            'de' => 'DE-30',
                        ],
                        'array' => [
                            (object) [
                                'en' => 'EN-40',
                                'de' => 'DE-40',
                            ],
                            (object) [
                                'en' => 'EN-50',
                                'de' => 'DE-50',
                            ],
                            (object) [
                                'en' => 'EN-60',
                                'de' => 'DE-60',
                            ],
                        ],
                    ],
                    (object) [
                        'field' => (object) [
                            'en' => 'EN-70',
                            'de' => 'DE-70',
                        ],
                        'array' => [],
                    ],
                ]
            ]
        ];
    }
}
