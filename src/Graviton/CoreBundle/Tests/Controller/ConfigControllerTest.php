<?php
/**
 * functional test for /core/config
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/config.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ConfigControllerTest extends RestTestCase
{

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                'GravitonDyn\ConfigBundle\DataFixtures\MongoDB\LoadConfigData'
            )
        );
    }

    /**
     * We need to make sure that our Link headers are properly encoded for our RQL parser.
     * This test tries to ensure that as we have resources named-like-this in /core/config.
     *
     * @param string $expression         expression
     * @param string $expectedExpression expected expression
     * @param int    $resultCount        expected res count
     *
     * @dataProvider rqlCheckDataProvider
     *
     * @return void
     */
    public function testLinkHeaderEncodingDash($expression, $expectedExpression, $resultCount)
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/config/?'.$expression);
        $response = $client->getResponse();

        $this->assertStringContainsString($expectedExpression, $response->headers->get('Link'));
        $this->assertEquals($resultCount, count($client->getResults()));
    }

    /**
     * Data provider for self-Link-header check
     *
     * @return array data
     */
    public function rqlCheckDataProvider()
    {
        return array(
            array(
                'eq(id,'.$this->encodeString('tablet-hello-message').')',
                'eq(id,string:'.$this->encodeString('tablet-hello-message').')',
                1
            ),
            array(
                'eq(id,'.$this->encodeString('admin-additional+setting').')',
                'eq(id,string:'.$this->encodeString('admin-additional+setting').')',
                1
            ),
            array(
                'like(key,'.$this->encodeString('hello-').'*)',
                'like(key,'.$this->encodeString('hello-').'*)',
                1
            )
        );
    }

    /**
     * Encodes our expressions
     *
     * @param string $value value
     *
     * @return string encoded value
     */
    private function encodeString($value)
    {
        return str_replace(
            array('-', '_', '.', '~'),
            array('%2D', '%5F', '%2E', '%7E'),
            rawurlencode($value)
        );
    }
}
