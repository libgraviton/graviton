<?php
/**
 * TranslatableRequiredTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     *
     * @return void
     */
    public function testPutMethodIncludeRequiredTranslatable($data, $complainField)
    {
        $client = static::createRestClient();
        $client->put('/testcase/translatable-required/testdata', $data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertSame('children['.$complainField.']', $client->getResults()[0]->propertyPath);
        $this->assertSame('This value is not valid.', $client->getResults()[0]->message);
    }

    /**
     * Posts that the backend shall accept
     *
     * @return array data
     */
    public function acceptableDataProvider()
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
    public function unacceptableDataProvider()
    {
        return [
            'omit-required' => [
                'data' => [
                    'id' => 'testdata',
                    'optional' => [
                        'en' => 'Test'
                    ]
                ],
                'complainField' => 'required'
            ],
            'empty-optional' => [
                'data' => [
                    'id' => 'testdata',
                    'optional' => [],
                    'required' => [
                        'en' => 'Test'
                    ]
                ],
                'complainField' => 'optional'
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
                'complainField' => 'optional'
            ]
        ];
    }
}
