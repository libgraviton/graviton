<?php
/**
 * TranslatableArrayControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

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
    public function setUp() : void
    {
        if (!class_exists(TestCaseTranslatableArray::class)) {
            $this->markTestSkipped(sprintf('%s definition is not loaded', TestCaseTranslatableArray::class));
        }

        $this->loadFixturesLocal(
            [
                LoadTestCaseTranslatableArrayData::class,
            ]
        );
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
        $this->getWebRequest($client, $location, ['en', 'de']);
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
        $this->getWebRequest($client, '/testcase/translatable-array/testdata', ['en', 'de']);
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
                ]
            ],
            $client->getResults()
        );
    }

    /**
     * Make a get request
     *
     * @param Client $client    HTTP client
     * @param string $url       URL
     * @param array  $languages Languages
     * @return void
     */
    private function getWebRequest(Client $client, $url, array $languages = ['en'])
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
                'fr' => 'EN-10'
            ],
            'array' => [
                (object) [
                    'en' => 'EN-20',
                    'de' => 'DE-20',
                    'fr' => 'EN-20'
                ],
            ],
            'deep'  => (object) [
                'deep' => [
                    (object) [
                        'field' => (object) [
                            'en' => 'EN-30',
                            'de' => 'DE-30',
                            'fr' => 'EN-30'
                        ],
                        'array' => [
                            (object) [
                                'en' => 'EN-40',
                                'de' => 'DE-40',
                                'fr' => 'EN-40'
                            ],
                            (object) [
                                'en' => 'EN-50',
                                'de' => 'DE-50',
                                'fr' => 'EN-50'
                            ],
                            (object) [
                                'en' => 'EN-60',
                                'de' => 'DE-60',
                                'fr' => 'EN-60'
                            ],
                        ],
                    ],
                    (object) [
                        'field' => (object) [
                            'en' => 'EN-70',
                            'de' => 'DE-70',
                            'fr' => 'EN-70'
                        ]
                    ],
                ]
            ]
        ];
    }
}
