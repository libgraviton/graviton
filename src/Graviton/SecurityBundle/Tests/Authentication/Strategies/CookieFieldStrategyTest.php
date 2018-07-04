<?php
/**
 * Fetching authentication information from Cookie header.
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Class CookieFieldStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CookieFieldStrategyTest extends RestTestCase
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

        /** @var \Symfony\Bundle\FrameworkBundle\Client client */
        $this->client = static::createClient();
        $this->propertyKey = $this->client->getKernel()
            ->getContainer()
            ->getParameter('graviton.security.authentication.strategy.cookie.key');
        $this->strategy = new CookieFieldStrategy(
            $this->propertyKey
        );
    }

    /**
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy::apply
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
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
            [], //parameters
            [], //files
            [] //server
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
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy::apply
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy::extractAdUsername
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy::extractCoreId
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     *
     * @dataProvider stringExtractProvider
     *
     * @param string $username   Username to be found
     * @param string $fieldValue value to check
     *
     * @return void
     */
    public function testApplyExtract($username, $fieldValue)
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
            [], //parameters
            [], //files
            [] //server
        );

        $this->assertSame($username, $this->strategy->apply($this->client->getRequest()));
    }

    /**
     * @return array<string>
     */
    public function stringExtractProvider()
    {
        return array(
            'testing extract username' => array("someId123_test", "username=testUser;finnova_id=someId123_test"),
            'testing extract rev username' => array("someId123_foo", "finnova_id=someId123_foo;username=someOtherUser"),
            'trailing simicolon test' => array("someId123_bar", "finnova_id=someId123_bar;username=test-mdm;"),
        );
    }
}
