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
     * @return void
     */
    public function testValueInitializer()
    {
        $docId = uniqid('test');
        $data = [
            'id' => $docId
        ];

        $client = static::createRestClient();
        $client->put('/testcase/value-initializer/'.$docId, $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/value-initializer/'.$docId);

        $obj = $client->getResults();
        $this->assertNotNull($obj, $obj->currentDateField);

        // see if we can parse it
        $this->assertInstanceOf(
            \DateTime::class,
            \DateTime::createFromFormat(DateTimeInterface::ATOM, $obj->currentDateField)
        );

        // try to update it.. (change the date)
        $client = static::createRestClient();
        $client->put('/testcase/value-initializer/'.$docId, $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }
}
