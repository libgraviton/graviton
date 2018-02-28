<?php
/**
 * check to see if reading from header field works
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Class HeaderFieldStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HeaderFieldStrategyTest extends RestTestCase
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
        $client = static::createClient();
        $headerFieldName = $client->getKernel()
            ->getContainer()->getParameter('graviton.security.authentication.strategy.header.key');

        $server = array(
            'HTTP_'.strtoupper($headerFieldName) => $headerFieldValue,
        );

        $client->request(
            'GET', //method
            '/', //uri
            array(), //parameters
            array(), //files
            $server
        );

        $strategy = new HeaderFieldStrategy(
            $client->getKernel()->getContainer()->getParameter('graviton.security.authentication.strategy.header.key')
        );

        $this->assertEquals(
            $headerFieldValue,
            $strategy->apply($client->getRequest())
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
