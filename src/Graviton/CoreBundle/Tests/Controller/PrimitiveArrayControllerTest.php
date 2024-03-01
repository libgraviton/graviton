<?php
/**
 * PrimitiveArrayControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;
use GravitonDyn\TestCasePrimitiveArrayBundle\DataFixtures\MongoDB\LoadTestCasePrimitiveArrayData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class PrimitiveArrayControllerTest extends RestTestCase
{
    const DATE_FORMAT = 'Y-m-d\\TH:i:sO';

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        if (!class_exists(LoadTestCasePrimitiveArrayData::class)) {
            $this->markTestSkipped('TestCasePrimitiveArray definition is not loaded');
        }

        $this->loadFixturesLocal(
            [LoadTestCasePrimitiveArrayData::class]
        );
    }

    /**
     * Test GET one method
     *
     * @return void
     */
    public function testCheckGetOne()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/primitivearray/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($client->getResults());

        $this->assertFixtureData($client->getResults());
    }

    /**
     * Test GET all method
     *
     * @return void
     */
    public function testCheckGetAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/primitivearray/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $client->getResults());

        $this->assertFixtureData($client->getResults()[0]);
    }

    /**
     * Test POST method
     *
     * @return void
     */
    public function testPostMethod()
    {
        $data = (object) [
            'intarray'  => [10, 20],
            'strarray'  => ['a', 'b'],
            'boolarray' => [true, false],
            'hasharray' => [(object) ['x' => 'y'], (object) []],
            'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],

            'hash'      => (object) [
                'intarray'  => [10, 20],
                'strarray'  => ['a', 'b'],
                'boolarray' => [true, false],
                'hasharray' => [(object) ['x' => 'y'], (object) []],
                'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],
            ],

            'arrayhash' => [
                (object) [
                    'intarray'  => [10, 20],
                    'strarray'  => ['a', 'b'],
                    'boolarray' => [true, false],
                    'hasharray' => [(object) ['x' => 'y'], (object) []],
                    'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],
                ]
            ],

            'rawData'      => (object) [
                'hasharray' => [(object) ['x' => 'y'], (object) []],
                'emptyhash' => (object) [],
                'emptystring' => "",
                'emptyarray' => [],
                'emptyarrayhash' => [ (object) [] ]
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/primitivearray/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $client->request('GET', $location);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $result = $client->getResults();
        $this->assertNotNull($result->id);
        unset($result->id);
        $this->assertEquals($this->fixDateTimezone($data), $result);
    }

    /**
     * Test PUT method
     *
     * @return void
     */
    public function testPutMethod()
    {
        $data = (object) [
            'id'        => 'testdata',

            'intarray'  => [10, 20],
            'strarray'  => ['a', 'b'],
            'boolarray' => [true, false],
            'hasharray' => [(object) ['x' => 'y'], (object) []],
            'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],

            'hash'      => (object) [
                'intarray'  => [10, 20],
                'strarray'  => ['a', 'b'],
                'boolarray' => [true, false],
                'hasharray' => [(object) ['x' => 'y'], (object) []],
                'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],
            ],

            'arrayhash' => [
                (object) [
                    'intarray'  => [10, 20],
                    'strarray'  => ['a', 'b'],
                    'boolarray' => [true, false],
                    'hasharray' => [(object) ['x' => 'y'], (object) []],
                    'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],
                ]
            ],

            'rawData'      => (object) [],
        ];

        $client = static::createRestClient();
        $client->put('/testcase/primitivearray/testdata', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/primitivearray/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertEquals($this->fixDateTimezone($data), $client->getResults());
    }

    /**
     * always send one field wrong in toplevel, hash and arrayhash
     *
     * @return array|\Generator data
     */
    public static function validationDataProvider(): array|\Generator
    {
        // all is right!
        $baseObject = [
            'intarray'  => [10, 20],
            'strarray'  => ['a', 'b'],
            'boolarray' => [true, false],
            'hasharray' => [(object) ['x' => 'y'], (object) []],
            'datearray' => ['2015-09-30T23:59:59+0000', '2015-10-01T00:00:01+0300'],
        ];

        // we iterate these, each for top-level, hash and array! here we have wrong items
        $iterations = [
            'intarray'  => [10, 'a'],
            'strarray'  => ['a', 10],
            'boolarray' => [true, 'hans'],
            'hasharray' => [(object) ['x' => 'y'], false],
            'datearray' => ['2015-09-30T23:59:59+0000', 'sss'],
        ];

        foreach ($iterations as $fieldName => $wrongValue) {
            $data = array_merge(
                $baseObject,
                [
                    $fieldName => $wrongValue
                ]
            );

            // wrong field itself
            yield 'wrong-'.$fieldName => [
                $data,
                $fieldName.'.1'
            ];

            // now same for 'hash'!
            yield 'wrong-hash-'.$fieldName => [
                [
                    'hash' => $data
                ],
                'hash.'.$fieldName.'.1'
            ];

            // same for arrayhash
            yield 'wrong-arrayhash-'.$fieldName => [
                [
                    'arrayhash' => [
                        $data
                    ]
                ],
                'arrayhash.0.'.$fieldName.'.1'
            ];
        }
    }

    /**
     * Test validation
     *
     * @dataProvider validationDataProvider
     *
     * @return void
     */
    public function testValidation($data, $complainField)
    {
        $client = static::createRestClient();
        $client->put('/testcase/primitivearray/testdata', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertNotNull($client->getResults());

        if (!empty($complainField)) {
            $this->assertEquals(
                $complainField,
                $client->getResults()[1]->propertyPath
            );
        }
    }

    /**
     * Fix date timezone
     *
     * @param object $data Request data
     * @return object
     */
    private function fixDateTimezone($data)
    {
        $converter = function (&$date) {
            $date = \DateTime::createFromFormat(self::DATE_FORMAT, $date)
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                ->format(self::DATE_FORMAT);
        };

        array_walk($data->datearray, $converter);
        array_walk($data->hash->datearray, $converter);
        array_walk($data->arrayhash[0]->datearray, $converter);

        return $data;
    }


    /**
     * Assert fixture data
     *
     * @param object $data Fixture data
     * @return void
     */
    private function assertFixtureData($data)
    {
        foreach ([
                     $data,
                     $data->hash,
                     $data->arrayhash[0],
                 ] as $data) {
            $this->assertIsArray($data->intarray);
            foreach ($data->intarray as $value) {
                $this->assertIsInt($value);
            }

            $this->assertIsArray($data->strarray);
            foreach ($data->strarray as $value) {
                $this->assertIsString($value);
            }

            $this->assertIsArray($data->boolarray);
            foreach ($data->boolarray as $value) {
                $this->assertIsBool($value);
            }

            $this->assertIsArray($data->datearray);
            foreach ($data->datearray as $value) {
                $this->assertIsString($value);
                $this->assertInstanceOf(\DateTime::class, \DateTime::createFromFormat(self::DATE_FORMAT, $value));
            }

            $this->assertIsArray($data->hasharray);
            foreach ($data->hasharray as $value) {
                $this->assertIsObject($value);
            }
        }
    }

    /**
     * Assert item schema
     *
     * @param object $schema Item schema
     * @return void
     */
    private function assertItemSchema($schema)
    {
        foreach ([
                     $schema->properties,
                     $schema->properties->hash->properties,
                     $schema->properties->arrayhash->items->properties,
                 ] as $schema) {
            $this->assertEquals('array', $schema->intarray->type);
            $this->assertEquals('integer', $schema->intarray->items->type);

            $this->assertEquals('array', $schema->strarray->type);
            $this->assertEquals('string', $schema->strarray->items->type);

            $this->assertEquals('array', $schema->boolarray->type);
            $this->assertEquals('boolean', $schema->boolarray->items->type);

            $this->assertEquals('array', $schema->datearray->type);
            $this->assertEquals('string', $schema->datearray->items->type);
            $this->assertEquals('date-time', $schema->datearray->items->format);

            $this->assertEquals('array', $schema->hasharray->type);
            $this->assertEquals('object', $schema->hasharray->items->type);
        }
    }
}
