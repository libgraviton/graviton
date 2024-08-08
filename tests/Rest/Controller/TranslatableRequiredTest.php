<?php
/**
 * TranslatableRequiredTest class file
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatableRequiredTest extends RestTestCase
{

    /**
     * test stuff that the backend should accept
     *
     * @dataProvider acceptableDataProvider
     *
     * @param array $data data to post
     *
     * @return void
     */
    public function testPutWithAcceptableTranslatableRequests($data)
    {
        $client = static::createRestClient();
        $client->put('/testcase/translatable-required/testdata', $data);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());
    }

    /**
     * test stuff that the backend should accept
     *
     * @dataProvider unacceptableDataProvider
     *
     * @param array  $data          data to post
     * @param string $complainField field to complain about
     * @param string $errorMessage  error message
     *
     * @return void
     */
    public function testPutMethodIncludeRequiredTranslatable($data, $complainField, $errorMessage)
    {
        $client = static::createRestClient();
        $client->put('/testcase/translatable-required/testdata', $data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertSame($complainField, $client->getResults()[1]->propertyPath);
        $this->assertStringContainsString($errorMessage, $client->getResults()[1]->message);
    }

    /**
     * Posts that the backend shall accept
     *
     * @return array data
     */
    public static function acceptableDataProvider(): array
    {
        return [
            'omit-optional' => [
                'data' => [
                    'id' => 'testdata',
                    'required' => [
                        'en' => 'Test'
                    ]
                ]
            ],
            'with-optional' => [
                'data' => [
                    'id' => 'testdata',
                    'optional' => [
                        'en' => 'Test'
                    ]        ,
                    'required' => [
                        'en' => 'Test'
                    ]
                ]
            ]
        ];
    }

    /**
     * Posts that the backend shall NOT accept
     *
     * @return array data
     */
    public static function unacceptableDataProvider(): array
    {
        return [
            'omit-required' => [
                'data' => [
                    'id' => 'testdata',
                    'optional' => [
                        'en' => 'Test'
                    ]
                ],
                'complainField' => 'required',
                'errorMessage' => "Required property 'required' must be present in the object"
            ],
            'empty-optional' => [
                'data' => [
                    'id' => 'testdata',
                    'optional' => [],
                    'required' => [
                        'en' => 'Test'
                    ]
                ],
                'complainField' => 'optional.en',
                'errorMessage' => "Required property 'en' must be present in the object"
            ],
            'empty-no-default' => [
                'data' => [
                    'id' => 'testdata',
                    'optional' => [
                        'es' => 'Vamos a la playa'
                    ],
                    'required' => [
                        'en' => 'Test'
                    ]
                ],
                'complainField' => 'optional.en',
                'errorMessage' => "Required property 'en' must be present in the object"
            ]
        ];
    }
}
