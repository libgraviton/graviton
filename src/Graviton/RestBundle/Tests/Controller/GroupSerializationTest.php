<?php
/**
 * test class for the "group" serialization feature
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseGroupSerializationBundle\DataFixtures\MongoDB\LoadTestCaseGroupSerializationData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GroupSerializationTest extends RestTestCase
{

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            [
                LoadTestCaseGroupSerializationData::class
            ],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * test if our group settings are visible in schema
     *
     * @return void
     */
    public function testSchemaPresence()
    {
        $client = static::createRestClient();
        $client->request('GET', '/schema/testcase/group-serialization/item');
        $results = $client->getResults();

        $this->assertEquals([], $results->properties->field1->{'x-groups'});
        $this->assertEquals(['hans'], $results->properties->field2->{'x-groups'});
        $this->assertEquals(['fred'], $results->properties->field3->{'x-groups'});
        $this->assertEquals(['hans', 'fred'], $results->properties->field4->{'x-groups'});
    }

    /**
     * check handling of the serializer group implementation and header
     *
     * @param array  $data        data
     * @param string $headerValue header value
     * @param string $rql         rql
     *
     * @dataProvider gsDataProvider
     *
     * @return void
     */
    public function testCorrectGroupSerialization($data, $headerValue, $rql)
    {
        $client = static::createRestClient();

        if (is_null($headerValue)) {
            $serverVars = [];
        } else {
            $serverVars['HTTP_X-GROUPS'] = $headerValue;
        }

        $url = '/testcase/group-serialization/';
        if (!is_null($rql)) {
            $url .= '?'.$rql;
        }

        $client->request('GET', $url, [], [], $serverVars);
        $results = $client->getResults();

        $this->assertCount(2, $results);

        foreach ($data as $singleResult) {
            $this->assertContains($singleResult, $results, '', false, false);
        }
    }

    /**
     * data provider for test
     *
     * @return array data
     */
    public function gsDataProvider()
    {
        return [
            'default' => [
                [
                    (object) [
                        'field1' => 'value1'
                    ],
                    (object) [
                        'field1' => 'second-value1'
                    ]
                ],
                null,
                null
            ],
            'group-hans' => [
                [
                    (object) [
                        'field1' => 'value1',
                        'field2' => 'value2',
                        'field4' => 'value4'
                    ],
                    (object) [
                        'field1' => 'second-value1',
                        'field2' => 'second-value2',
                        'field4' => 'second-value4'
                    ]
                ],
                'hans',
                null
            ],
            'group-fred' => [
                [
                    (object) [
                        'field1' => 'value1',
                        'field3' => 'value3',
                        'field4' => 'value4'
                    ],
                    (object) [
                        'field1' => 'second-value1',
                        'field3' => 'second-value3',
                        'field4' => 'second-value4'
                    ]
                ],
                'fred',
                null
            ],
            'group-hans-fred' => [
                [
                    (object) [
                        'field1' => 'value1',
                        'field2' => 'value2',
                        'field3' => 'value3',
                        'field4' => 'value4'
                    ],
                    (object) [
                        'field1' => 'second-value1',
                        'field2' => 'second-value2',
                        'field3' => 'second-value3',
                        'field4' => 'second-value4'
                    ]
                ],
                'hans, fred',
                null
            ],
            'group-hans-rql-not-allowed-field' => [
                [
                    (object) [
                    ],
                    (object) [
                    ]
                ],
                'hans',
                'select(field3)'
            ],
            'group-fred-rql-allowed-field' => [
                [
                    (object) [
                        'field3' => 'value3'
                    ],
                    (object) [
                        'field3' => 'second-value3'
                    ]
                ],
                'fred',
                'select(field3)'
            ]
        ];
    }
}
