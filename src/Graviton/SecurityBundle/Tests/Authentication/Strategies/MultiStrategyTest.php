<?php
/**
 * Handling authentication for clients in the same network.
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Class MultiStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MultiStrategyTest extends RestTestCase
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
        $propertyKey = $this->client->getKernel()
            ->getContainer()
            ->getParameter('graviton.security.authentication.strategy.subnet.key');
        $sameSubnetStrategy = new SameSubnetStrategy($propertyKey);
        $this->propertyKey = $this->client->getKernel()
            ->getContainer()
            ->getParameter('graviton.security.authentication.strategy.cookie.key');
        $cookieFieldStrategy = new CookieFieldStrategy($this->propertyKey);

        $this->strategy = new MultiStrategy(
            [
                $sameSubnetStrategy,
                $cookieFieldStrategy,
            ]
        );
    }

    /**
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\MultiStrategy::apply
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     *
     * @return void
     */
    public function testApply()
    {
        $fieldValue = 'exampleAuthenticationHeader';

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
}
