<?php
/**
 * Handling authentication for clients in the same network.
 */

namespace Graviton\SecurityBundle\Tests\Authentication\Strategies;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class SameSubnetStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SameSubnetStrategyTest extends RestTestCase
{
    protected $strategy;
    /** @var KernelBrowser */
    protected $client;
    protected $propertyKey;

    /**
     * UnitTest Starts this on reach test
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->propertyKey = $this->client->getKernel()
                                          ->getContainer()
                                          ->getParameter('graviton.security.authentication.strategy.subnet.key');
        $this->strategy = new SameSubnetStrategy(
            $this->propertyKey
        );
    }

    /**
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\SameSubnetStrategy::apply
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     *
     * @return void
     */
    public function testApplyHeader()
    {
        $client = clone static::createClient();
        $client->setServerParameter('HTTP_X-GRAVITON-AUTHENTICATION', 'test-user-name');
        $this->strategy->setSubnetIp('127.0.0.0/7');
        $client->request('GET', '/');

        $this->assertSame('test-user-name', $this->strategy->apply($client->getRequest()));
    }

    /**
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\SameSubnetStrategy::apply
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::extractFieldInfo
     * @covers \Graviton\SecurityBundle\Authentication\Strategies\AbstractHttpStrategy::validateField
     *
     * @return void
     */
    public function testApplyHeaderReturnEmpty()
    {
        $client = clone static::createClient([]);
        $client->setServerParameter('HTTP_X-GRAVITON-AUTHENTICATION', 'test-user-name');
        $client->setServerParameter('REMOTE_ADDR', '126.0.0.1');
        $client->request('GET', '/');

        $this->assertSame('', $this->strategy->apply($client->getRequest()));
    }
}
