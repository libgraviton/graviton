<?php
/**
 * integration tests for our schema variation feature
 */

namespace Graviton\SchemaBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaVariationsTest extends RestTestCase
{
    /**
     * tests schema variations
     *
     * @param object $data           data to send
     * @param int    $expectedStatus expected response status
     * @param array  $responseChecks some text checks to perform
     *
     * @dataProvider schemaVariationsDataProvider
     *
     * @return void
     */
    public function testSchemaVariation($data, $expectedStatus, array $responseChecks)
    {
        $client = static::createRestClient();
        $client->post('/testcase/schemavariation/', $data);
        $this->assertEquals($expectedStatus, $client->getResponse()->getStatusCode());

        $response = (string) $client->getResponse()->getContent();

        if (is_array($responseChecks) && !empty($responseChecks)) {
            foreach ($responseChecks as $responseCheck) {
                $this->assertContains($responseCheck, $response);
            }
        } else {
            $this->assertEmpty($response);
        }
    }

    /**
     * Data provider
     *
     * @return array data
     */
    public function schemaVariationsDataProvider()
    {
        return [
            'single-ok' => [
                'data' => (object) [
                    'recordType' => 1,
                    'isLivingAlone' => false,
                    'recordNumber' => 2,
                    'name' => 'Name',
                    'secondName' => 'Name 2'
                ],
                'status' => 201,
                'checks' => [
                ]
            ],
            'single-nok1' => [
                'data' => (object) [
                    'recordType' => 1,
                    'recordNumber' => 2,
                    'name' => 'Name',
                    'secondName' => 'Name 2'
                ],
                'status' => 400,
                'checks' => [
                    'isLivingAlone'
                ]
            ],
            'single-nok2' => [
                'data' => (object) [
                    'recordType' => 1,
                    'name' => 'Name',
                    'secondName' => 'Name 2'
                ],
                'status' => 400,
                'checks' => [
                    'isLivingAlone',
                    'recordNumber'
                ]
            ],
            'between-ok' => [
                'data' => (object) [
                    'recordType' => 2,
                    'recordNumber' => 22
                ],
                'status' => 201,
                'checks' => [
                ]
            ],
            'between-nok' => [
                'data' => (object) [
                    'recordType' => 2
                ],
                'status' => 400,
                'checks' => [
                    'recordNumber'
                ]
            ],
            'multiple-ok' => [
                'data' => (object) [
                    'recordType' => 3,
                    'name' => 'Fred',
                    'secondName' => 'Feuz'
                ],
                'status' => 201,
                'checks' => [
                ]
            ],
            'multiple-nok1' => [
                'data' => (object) [
                    'recordType' => 4,
                    'secondName' => 'Feuz'
                ],
                'status' => 400,
                'checks' => [
                    'name'
                ]
            ],
            'multiple-nok2' => [
                'data' => (object) [
                    'recordType' => 5
                ],
                'status' => 400,
                'checks' => [
                    'name',
                    'secondName'
                ]
            ],
            'upper-nok' => [
                'data' => (object) [
                    'recordType' => 11
                ],
                'status' => 400,
                'checks' => [
                    'recordNumber'
                ]
            ]
        ];
    }
}
