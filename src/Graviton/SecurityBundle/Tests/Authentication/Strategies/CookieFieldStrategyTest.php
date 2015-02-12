<?php
/**
 * check if reading from cookie works
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\SecurityBundle\Tests\GravitonSecurityBundleTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Class CookieFieldStrategyTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CookieFieldStrategyTest extends GravitonSecurityBundleTestCase
{
    /**
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy::apply
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     *
     * @dataProvider stringProvider
     *
     * @param string $fieldValue value to check
     *
     * @return void
     */
    public function testApply($fieldValue)
    {
        $client = static::createClient();
        $cookie = new Cookie(
            CookieFieldStrategy::COOKIE_FIELD,
            $fieldValue,
            time() + 3600 * 24 * 7,
            '/',
            null,
            false,
            false
        );
        $client->getCookieJar()->set($cookie);
        $client->request(
            'GET', //method
            '/', //uri
            array(), //parameters
            array(), //files
            array() //server
        );

        $strategy = new CookieFieldStrategy();

        $this->assertSame($fieldValue, $strategy->apply($client->getRequest(), CookieFieldStrategy::COOKIE_FIELD));
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
