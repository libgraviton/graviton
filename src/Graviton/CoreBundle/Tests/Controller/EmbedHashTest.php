<?php
/**
 * EmbedHashTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EmbedHashTest extends RestTestCase
{
    /**
     * @param object $data JSON data
     * @return void
     *
     * @dataProvider dataValid
     * @group newEmbed
     * @group newEmbedValid
     * @group newEmbedHash
     * @group newEmbedHashValid
     */
    public function testValid($data)
    {
        $client = static::createRestClient();
        $client->post('/testcase/embed-hash/', $data);

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    /**
     * @return array
     */
    public function dataValid()
    {
        return [
            'all' => [
                (object) [
                    'value'         => 'value',
                    'defaultHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'defaultHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'defaultHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'defaultHash.subRequiredHash.value'],
                    ],
                    'optionalHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'optionalHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'optionalHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'optionalHash.subRequiredHash.value'],
                    ],
                    'requiredHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'requiredHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'requiredHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'requiredHash.subRequiredHash.value'],
                    ],
                ],
            ],
            'no optionalHash' => [
                (object) [
                    'value'         => 'value',
                    'defaultHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'defaultHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'defaultHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'defaultHash.subRequiredHash.value'],
                    ],
                    'requiredHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'requiredHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'requiredHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'requiredHash.subRequiredHash.value'],
                    ],
                ],
            ],
            'no subOptionalHash' => [
                (object) [
                    'value'         => 'value',
                    'defaultHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'defaultHash.subDefaultHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'defaultHash.subRequiredHash.value'],
                    ],
                    'optionalHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'optionalHash.subDefaultHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'optionalHash.subRequiredHash.value'],
                    ],
                    'requiredHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'requiredHash.subDefaultHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'requiredHash.subRequiredHash.value'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param object   $data   JSON data
     * @param object[] $errors Expected errors
     * @return void
     *
     * @dataProvider dataInvalid
     * @group newEmbed
     * @group newEmbedInvalid
     * @group newEmbedHash
     * @group newEmbedHashInvalid
     */
    public function testInvalid($data, array $errors)
    {
        $client = static::createRestClient();
        $client->post('/testcase/embed-hash/', $data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(count($client->getResults()), count($errors));

        foreach ($errors as $error) {
            $this->assertContains($error, $client->getResults(), '', false, false);
        }
    }

    /**
     * @return array
     */
    public function dataInvalid()
    {
        return [
            'no value' => [
                (object) [
                    'defaultHash'   => (object) [
                        'subDefaultHash'    => (object) ['value' => 'defaultHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'defaultHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'defaultHash.subRequiredHash.value'],
                    ],
                    'optionalHash'   => (object) [
                        'subDefaultHash'    => (object) ['value' => 'optionalHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'optionalHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'optionalHash.subRequiredHash.value'],
                    ],
                    'requiredHash'   => (object) [
                        'subDefaultHash'    => (object) ['value' => 'requiredHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'requiredHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'requiredHash.subRequiredHash.value'],
                    ],
                ],
                [
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'defaultHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'optionalHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'requiredHash.value',
                    ],
                ],
            ],
            'no value at all' => [
                (object) [
                    'defaultHash'   => (object) [
                        'subDefaultHash'    => (object) [],
                        'subOptionalHash'   => (object) [],
                        'subRequiredHash'   => (object) [],
                    ],
                    'optionalHash'   => (object) [
                        'subDefaultHash'    => (object) [],
                        'subOptionalHash'   => (object) [],
                        'subRequiredHash'   => (object) [],
                    ],
                    'requiredHash'   => (object) [
                        'subDefaultHash'    => (object) [],
                        'subOptionalHash'   => (object) [],
                        'subRequiredHash'   => (object) [],
                    ],
                ],
                [
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'value',
                    ],

                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'defaultHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'defaultHash.subDefaultHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'defaultHash.subOptionalHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'defaultHash.subRequiredHash.value',
                    ],

                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'optionalHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'optionalHash.subDefaultHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'optionalHash.subOptionalHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'optionalHash.subRequiredHash.value',
                    ],

                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'requiredHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'requiredHash.subDefaultHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'requiredHash.subOptionalHash.value',
                    ],
                    (object) [
                        'message'       => 'The property value is required',
                        'propertyPath'  => 'requiredHash.subRequiredHash.value',
                    ],
                ],
            ],
            'no requiredHash' => [
                (object) [
                    'value'         => 'value',
                    'defaultHash'   => (object) [
                        'value'             => 'defaultHash.value',
                        'subDefaultHash'    => (object) ['value' => 'defaultHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'defaultHash.subOptionalHash.value'],
                    ],
                    'optionalHash'   => (object) [
                        'value'             => 'optionalHash.value',
                        'subDefaultHash'    => (object) ['value' => 'optionalHash.subDefaultHash.value'],
                        'subOptionalHash'   => (object) ['value' => 'optionalHash.subOptionalHash.value'],
                    ],
                ],
                [
                    (object) [
                        'message'       => 'The property requiredHash is required',
                        'propertyPath'  => 'requiredHash',
                    ],
                    (object) [
                        'message'       => 'The property subRequiredHash is required',
                        'propertyPath'  => 'defaultHash.subRequiredHash',
                    ],
                    (object) [
                        'message'       => 'The property subRequiredHash is required',
                        'propertyPath'  => 'optionalHash.subRequiredHash',
                    ],
                ],
            ],
            'no defaultHash' => [
                (object) [
                    'value'         => 'value',
                    'optionalHash'   => (object) [
                        'value'             => 'optionalHash.value',
                        'subOptionalHash'   => (object) ['value' => 'optionalHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'optionalHash.subRequiredHash.value'],
                    ],
                    'requiredHash'   => (object) [
                        'value'             => 'requiredHash.value',
                        'subOptionalHash'   => (object) ['value' => 'requiredHash.subOptionalHash.value'],
                        'subRequiredHash'   => (object) ['value' => 'requiredHash.subRequiredHash.value'],
                    ],
                ],
                [
                    (object) [
                        'message'       => 'The property defaultHash is required',
                        'propertyPath'  => 'defaultHash',
                    ],
                    (object) [
                        'message'       => 'The property subDefaultHash is required',
                        'propertyPath'  => 'optionalHash.subDefaultHash',
                    ],
                    (object) [
                        'message'       => 'The property subDefaultHash is required',
                        'propertyPath'  => 'requiredHash.subDefaultHash',
                    ],
                ],
            ],
        ];
    }
}
