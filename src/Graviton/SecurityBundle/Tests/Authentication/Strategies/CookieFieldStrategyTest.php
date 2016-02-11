<?php
/**
 * check if reading from cookie works
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\TestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CookieFieldStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CookieFieldStrategyTest extends WebTestCase
{
    protected $strategy;
    protected $client;
    protected $propertyKey;

    /**
     * UnitTest Starts this on reach test
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->propertyKey = $this->client->getKernel()
            ->getContainer()->getParameter('graviton.security.authentication.strategy_key');
        $this->strategy = new CookieFieldStrategy(
            $this->propertyKey
        );

    }

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
        $cookie = new Cookie(
            $this->propertyKey,
            $fieldValue,
            time() + 3600 * 24 * 7,
            '/',
            null,
            false,
            false
        );
        $this->client->getCookieJar()->set($cookie);
        $this->client->request(
            'GET', //method
            '/', //uri
            array(), //parameters
            array(), //files
            array() //server
        );

        $this->assertSame($fieldValue, $this->strategy->apply($this->client->getRequest()));
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

    /**
     * Todo, find a way to have also to client id set in request stack.
     *
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy::apply
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     * @covers       \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::setDynamicParameters
     *
     * @dataProvider stringExtractProvider
     *
     * @param string $fieldValue value to check
     *
     * @return void
     */
    public function testApplyExtract($fieldValue)
    {
        $cookie = new Cookie(
            $this->propertyKey,
            $fieldValue,
            time() + 3600 * 24 * 7,
            '/',
            null,
            false,
            false
        );
        $this->client->getCookieJar()->set($cookie);
        $this->client->request(
            'GET', //method
            '/', //uri
            array(), //parameters
            array(), //files
            array() //server
        );

        $this->strategy->setDynamicParameters(new RequestStack(), 'username', false, false);
        $username = $this->strategy->apply($this->client->getRequest());
        $this->assertSame('testUser', $username);
    }

    /**
     * @return array<string>
     */
    public function stringExtractProvider()
    {
        return array(
            'testing extract username'       => array("username=testUser,core_client_id=someId123"),
            'testing extract rev username'   => array("core_client_id=someId123,username=testUser"),
        );
    }
}
