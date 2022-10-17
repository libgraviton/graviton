<?php
/**
 * ValuePatternTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValueInitializerTest extends RestTestCase
{

    /**
     * test regexes
     *
     * @param string $value                value
     * @param int    $expectedResponseCode code
     *
     * @dataProvider dataProvider
     *
     * @return void
     */
    public function testValueInitializer(string $type, ?int $checkLower = null, ?int $checkHigher = null)
    {
        $docId = uniqid('test');
        $data = [
            'id' => $docId,
            'type' => $type
        ];

        $client = static::createRestClient();
        $client->put('/testcase/value-initializer/'.$docId, $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/value-initializer/'.$docId);

        $obj = $client->getResults();
        $this->assertNotNull($obj, $obj->currentDateField);
        $this->assertNotNull($obj, $obj->expireDateField);

        $createDate = \DateTime::createFromFormat(DateTimeInterface::ATOM, $obj->currentDateField);
        $expireDate = \DateTime::createFromFormat(DateTimeInterface::ATOM, $obj->expireDateField);

        // see if we can parse it
        $this->assertInstanceOf(\DateTime::class, $createDate);
        $this->assertInstanceOf(\DateTime::class, $expireDate);

        // calculate diff between the two!
        $dateDiff = $expireDate->diff($createDate);
        $this->assertGreaterThan($checkLower, $dateDiff->days);
        $this->assertLessThan($checkHigher, $dateDiff->days);

        // try to change the type
        $existingObj = clone $obj;
        $existingObj->type = "newtype";

        $client = static::createRestClient();
        $client->put('/testcase/value-initializer/'.$docId, $existingObj);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // to other known type
        $existingObj->type = "anothertype";

        $client = static::createRestClient();
        $client->put('/testcase/value-initializer/'.$docId, $existingObj);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // try to update it.. (removing dates)
        $client = static::createRestClient();
        $client->put('/testcase/value-initializer/'.$docId, $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function dataProvider()
    {
        return [
            [
                'test1',
                29,
                31
            ],
            [
                'test2',
                299,
                301
            ]
        ];
    }
}
