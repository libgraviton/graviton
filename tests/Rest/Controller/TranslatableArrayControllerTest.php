<?php
/**
 * TranslatableArrayControllerTest class file
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\Client;
use Graviton\Tests\RestTestCase;
use GravitonDyn\TestCaseTranslatableArrayBundle\DataFixtures\MongoDB\LoadTestCaseTranslatableArrayData;
use GravitonDyn\TestCaseTranslatableArrayBundle\Document\TestCaseTranslatableArray;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * data provider
     *
     * @return \Generator gen
     */
    public static function validationDataProvider() : \Generator
    {
        $data = [
            'id'    => 'testdata',
            'field' => [
                'de' => 'No "en" translation',
            ],
            'array' => [
                'Invalid value',
                (object) ['Invalid' => 'value'],
            ],
            'deep'  => [
                'deep' => [
                    [
                        'field' => 'Invalid value',
                        'array' => 'Invalid value',
                    ],
                    [
                        'field' => [
                            'en' => 'Valid value',
                        ],
                        'array' => [
                            [
                                'en' => 'Valid value',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'first' => [$data, 'field.en'];

        $data['field']['en'] = 'val';

        yield 'with-en' => [$data, 'array.0'];

        $data['array'][0] = ['prop' => 'dude'];

        yield 'with-en-array-0' => [$data, 'array.0.en'];

        $data['array'][0] = ['en' => 'dude'];

        yield 'with-en-array-0-2' => [$data, 'array.1.en'];

        $data['array'][1] = ['en' => 'dude2'];

        yield 'with-en-array-3' => [$data, 'deep.deep.0.field'];

        $data['deep']['deep'][0]['field'] = ['en' => 'dude2'];

        yield 'with-deep-deep-field' => [$data, 'deep.deep.0.array'];

        $data['deep']['deep'][0]['array'] = [1, 2];

        yield 'with-deep-deep-array-field' => [$data, 'deep.deep.0.array.0'];

        $data['deep']['deep'][0]['array'] = [
            ['en' => 'dude2'],
            ['en' => 'dude2']
        ];

        yield 'with-deep-deep-array-field2' => [$data, null];
    }

    /**
     * Test validation
     *
     * @param array   $data          data
     * @param ?string $complainField complain field
     *
     * @return void
     */
    #[DataProvider("validationDataProvider")]
    public function testValidation(array $data, ?string $complainField)
    {

        $client = static::createRestClient();
        $client->put('/testcase/translatable-array/testdata', $data);

        if (!is_null($complainField)) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals($complainField, $client->getResults()[1]->propertyPath);
        } else {
            $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
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
