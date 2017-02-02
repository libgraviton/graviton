<?php
/**
 * test for IncrementalDateFieldConstraint
 */

namespace Graviton\SchemaBundle\Tests\ConstraintBuilder;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class IncrementalDateFieldConstraintTest extends RestTestCase
{

    /**
     * test the validation of the incrementalDate constraint
     *
     * @return void
     */
    public function testIncrementalDateFieldHandling()
    {
        // create the record
        $object = (object) [
            'id' => 'dude',
            'mightyDate' => '1984-05-02T07:00:01+0000'
        ];

        $client = static::createRestClient();
        $client->put('/testcase/incremental-date-constraint/dude', $object);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertNull($client->getResults());

        // foolish attempts to beat the validation
        $shouldNotWork = [
            'same' => '1984-05-02T07:00:01+0000',
            'timezone' => '1984-05-02T07:00:01+2000',
            '1sec' => '1984-05-02T07:00:00+0000',
            'string' => 'ss'
        ];

        foreach ($shouldNotWork as $date) {
            $object = (object) [
                'id' => 'dude',
                'mightyDate' => $date
            ];

            $client = static::createRestClient();
            $client->put('/testcase/incremental-date-constraint/dude', $object);

            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals(
                $client->getResults()[0],
                (object) [
                    'propertyPath' => 'mightyDate',
                    'message' => 'The date must be greater than the saved date 1984-05-02T07:00:01+0000'
                ]
            );

            // same with patch, no change made, same date if first validation goes through
            $patchObject = json_encode(
                [
                    [
                        'op' => 'replace',
                        'path' => '/mightyDate',
                        'value' => $date
                    ]
                ]
            );
            $client->request('PATCH', '/testcase/incremental-date-constraint/dude', [], [], [], $patchObject);

            if ($client->getResponse()->getStatusCode() == Response::HTTP_BAD_REQUEST) {
            $this->assertEquals(
                $client->getResults()[0],
                (object) [
                    'propertyPath' => 'mightyDate',
                    'message' => 'The date must be greater than the saved date 1984-05-02T07:00:01+0000'
                ]
            );
            } else {
                $this->assertEquals(Response::HTTP_NOT_MODIFIED, $client->getResponse()->getStatusCode());
            }
        }

        // this should work (+1 sec)
        $object = (object) [
            'id' => 'dude',
            'mightyDate' => '1984-05-02T07:00:02+0000'
        ];

        $client = static::createRestClient();
        $client->put('/testcase/incremental-date-constraint/dude', $object);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertNull($client->getResults());

        // should work via PATCH
        $patchObject = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/mightyDate',
                    'value' => '1984-05-02T07:00:03+0000'
                ]
            ]
        );
        $client = static::createRestClient();
        $client->request('PATCH', '/testcase/incremental-date-constraint/dude', [], [], [], $patchObject);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNull($client->getResults());
    }
}
