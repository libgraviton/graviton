<?php
/**
 * check to see if reading from header field works
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\TestBundle\Test\WebTestCase;

/**
 * Class HeaderFieldStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HeaderFieldStrategyTest extends WebTestCase
{
    /**
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\HeaderFieldStrategy::apply
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     *
     * @dataProvider stringProvider
     *
     * @param string $headerFieldValue header to test with
     *
     * @return void
     */
    public function testApply($headerFieldValue)
    {
        $server = array(
            'HTTP_X_IDP_USERNAME' => $headerFieldValue, //"example-authentication-header",
        );

        $client = static::createClient();
        $client->request(
            'GET', //method
            '/', //uri
            array(), //parameters
            array(), //files
            $server
        );

        $strategy = new HeaderFieldStrategy();

        $this->assertEquals(
            $headerFieldValue,
            $strategy->apply($client->getRequest(), HeaderFieldStrategy::X_HEADER_FIELD)
        );
    }

    /**
     * @return array<string>
     */
    public function stringProvider()
    {
        return array(
            'plain string, no special chars' => array('exampleAuthenticationHeader'),
            'string with special chars'      => array("$-_.+!*'(),{}|\\^~[]`<>#%;/?:@&=."),
            'string with octal chars'        => array("a: \141, A: \101"),
            'string with hex chars'          => array("a: \x61, A: \x41")
        );
    }
}
