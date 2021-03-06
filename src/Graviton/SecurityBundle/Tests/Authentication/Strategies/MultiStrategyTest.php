<?php
/**
 * Handling authentication for clients in the same network.
 */

namespace Graviton\SecurityBundle\Tests\Authentication\Strategies;

use Graviton\SecurityBundle\Authentication\Strategies\MultiStrategy;
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
    public function setUp() : void
    {
        parent::setUp();

        /** @var \Symfony\Bundle\FrameworkBundle\Client client */
        $this->client = static::createClient();
        $this->propertyKey = $this
            ->getContainer()
            ->getParameter('graviton.security.authentication.strategy.cookie.key');

        $this->strategy = new MultiStrategy();
        $this->strategy->setStrategies(
            $this->getContainer(),
            [
                'graviton.security.authentication.strategy.subnet',
                'graviton.security.authentication.strategy.cookie'
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
            '',
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
