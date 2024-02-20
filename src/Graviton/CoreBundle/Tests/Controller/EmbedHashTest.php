<?php
/**
 * EmbedHashTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @param object   $data          JSON data
     * @param object[] $propertyPaths property paths
     *
     * @return void
     *
     * @dataProvider dataInvalid
     * @group newEmbed
     * @group newEmbedInvalid
     * @group newEmbedHash
     * @group newEmbedHashInvalid
     */
    public function testInvalid($data, array $propertyPaths)
    {
        $client = static::createRestClient();
        $client->post('/testcase/embed-hash/', $data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // always +1 as we print auf '.body' separate!
        $this->assertEquals(count($client->getResults()), count($propertyPaths) + 1);

        foreach ($propertyPaths as $propertyPath) {
            $included = false;
            foreach ($client->getResults() as $singleError) {
                if ($singleError->propertyPath == $propertyPath) {
                    $included = true;
                }
            }
            $this->assertTrue($included);
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
                    'value'
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
                    'value'
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
                    'requiredHash'
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
                    'defaultHash'
                ],
            ],
        ];
    }
}
