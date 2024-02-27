<?php
/**
 * EmbedArrayTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EmbedArrayTest extends RestTestCase
{
    /**
     * @param object $data JSON data
     * @return void
     *
     * @dataProvider dataValid
     * @group newEmbed
     * @group newEmbedValid
     * @group newEmbedArray
     * @group newEmbedArrayValid
     */
    public function testValid($data)
    {
        $client = static::createRestClient();
        $client->post('/testcase/embed-array/', $data);

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
                    'defaultArray'  => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'defaultArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'defaultArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'defaultArray.subRequiredHash.value'],
                        ],
                    ],
                    'optionalArray' => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'optionalArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'optionalArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'optionalArray.subRequiredHash.value'],
                        ],
                    ],
                    'requiredArray' => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'requiredArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'requiredArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'requiredArray.subRequiredHash.value'],
                        ],
                    ],
                    'notEmptyArray' => [
                        (object) [
                            'value'             => 'notEmptyArray.value',
                            'subDefaultHash'    => (object) ['value' => 'notEmptyArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'notEmptyArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'notEmptyArray.subRequiredHash.value'],
                        ],
                    ],
                ],
            ],
            'all empty' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [],
                    'optionalArray' => [],
                    'requiredArray' => [],
                    'notEmptyArray' => [
                        (object) [
                            'value'             => 'notEmptyArray.value',
                            'subDefaultHash'    => (object) ['value' => 'notEmptyArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'notEmptyArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'notEmptyArray.subRequiredHash.value'],
                        ],
                    ],
                ],
            ],

            'no optionalArray' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [],
                    'requiredArray' => [],
                    'notEmptyArray' => [
                        (object) [
                            'value'             => 'notEmptyArray.value',
                            'subDefaultHash'    => (object) ['value' => 'notEmptyArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'notEmptyArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'notEmptyArray.subRequiredHash.value'],
                        ],
                    ],
                ],
            ],
            'no notEmptyArray' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [],
                    'requiredArray' => [],
                    'optionalArray' => [],
                ],
            ],
            'no subOptionalHash' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'defaultArray.subDefaultHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'defaultArray.subRequiredHash.value'],
                        ],
                    ],
                    'optionalArray' => [
                        (object) [
                            'value'             => 'optionalArray.value',
                            'subDefaultHash'    => (object) ['value' => 'optionalArray.subDefaultHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'optionalArray.subRequiredHash.value'],
                        ],
                    ],
                    'requiredArray' => [
                        (object) [
                            'value'             => 'requiredArray.value',
                            'subDefaultHash'    => (object) ['value' => 'requiredArray.subDefaultHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'requiredArray.subRequiredHash.value'],
                        ],
                    ],
                    'notEmptyArray' => [
                        (object) [
                            'value'             => 'notEmptyArray.value',
                            'subDefaultHash'    => (object) ['value' => 'notEmptyArray.subDefaultHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'notEmptyArray.subRequiredHash.value'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param object   $data          JSON data
     * @param object[] $propertyPaths property paths
     * @return void
     *
     * @dataProvider dataInvalid
     * @group newEmbed
     * @group newEmbedInvalid
     * @group newEmbedArray
     * @group newEmbedArrayInvalid
     */
    public function testInvalid($data, array $propertyPaths)
    {
        $client = static::createRestClient();
        $client->post('/testcase/embed-array/', $data);

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
            'empty notEmptyArray' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [],
                    'optionalArray' => [],
                    'requiredArray' => [],
                    'notEmptyArray' => [],
                ],
                [
                    'notEmptyArray'
                ]
            ],
            'no requiredArray' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [],
                    'optionalArray' => [],
                ],
                [
                    'requiredArray'
                ],
            ],
            'no value' => [
                (object) [
                    'defaultArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) ['value' => 'defaultHash.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'defaultHash.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'defaultHash.subRequiredHash.value'],
                        ],
                    ],
                    'optionalArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) ['value' => 'optionalHash.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'optionalHash.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'optionalHash.subRequiredHash.value'],
                        ],
                    ],
                    'requiredArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) ['value' => 'requiredHash.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'requiredHash.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'requiredHash.subRequiredHash.value'],
                        ],
                    ],
                    'notEmptyArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) ['value' => 'notEmptyArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'notEmptyArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'notEmptyArray.subRequiredHash.value'],
                        ],
                    ],
                ],
                [
                    'value'
                ],
            ],
            'no value at all' => [
                (object) [
                    'defaultArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) [],
                            'subOptionalHash'   => (object) [],
                            'subRequiredHash'   => (object) [],
                        ],
                    ],
                    'optionalArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) [],
                            'subOptionalHash'   => (object) [],
                            'subRequiredHash'   => (object) [],
                        ],
                    ],
                    'requiredArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) [],
                            'subOptionalHash'   => (object) [],
                            'subRequiredHash'   => (object) [],
                        ],
                    ],
                    'notEmptyArray'  => [
                        (object) [
                            'subDefaultHash'    => (object) [],
                            'subOptionalHash'   => (object) [],
                            'subRequiredHash'   => (object) [],
                        ],
                    ],
                ],
                [
                    'value'
                ]
            ],
            'no requiredHash' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'defaultArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'defaultArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) [],
                        ],
                    ],
                    'optionalArray' => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'optionalArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'optionalArray.subOptionalHash.value'],
                        ],
                    ],
                    'requiredArray' => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subDefaultHash'    => (object) ['value' => 'requiredArray.subDefaultHash.value'],
                            'subOptionalHash'   => (object) ['value' => 'requiredArray.subOptionalHash.value'],
                        ],
                    ],
                    'notEmptyArray' => [
                        (object) [
                            'value'             => 'notEmptyArray.value',
                            'subDefaultHash'    => (object) ['value' => 'notEmptyArray.subDefaultHash.value'],
                        ],
                    ],
                ],
                [
                    'defaultArray.0.subRequiredHash.value'
                ],
            ],
            'no defaultHash' => [
                (object) [
                    'value'         => 'value',
                    'defaultArray'  => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subOptionalHash'   => (object) ['value' => 'defaultArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'defaultArray.subRequiredHash.value'],
                        ],
                    ],
                    'optionalArray' => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subOptionalHash'   => (object) ['value' => 'optionalArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'optionalArray.subRequiredHash.value'],
                        ],
                    ],
                    'requiredArray' => [
                        (object) [
                            'value'             => 'defaultArray.value',
                            'subOptionalHash'   => (object) ['value' => 'requiredArray.subOptionalHash.value'],
                            'subRequiredHash'   => (object) ['value' => 'requiredArray.subRequiredHash.value'],
                        ],
                    ],
                    'notEmptyArray' => [
                        (object) [
                            'value'             => 'notEmptyArray.value',
                            'subRequiredHash'   => (object) ['value' => 'notEmptyArray.subRequiredHash.value'],
                        ],
                    ],
                ],
                [
                    'defaultArray.0.subDefaultHash'
                ],
            ],
        ];
    }
}
