<?php
/**
 * Handling authentication for clients in the same network.
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\TestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class SameSubnetStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SameSubnetStrategyTest extends WebTestCase
{
    protected $strategy;
    /** @var Client */
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
        $client = static::createClient([], ['HTTP_X-GRAVITON-AUTHENTICATION' => 'test-user-name']);
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
        $options = ['HTTP_X-GRAVITON-AUTHENTICATION' => 'test-user-name', 'REMOTE_ADDR' => '126.0.0.1'];
        $client = static::createClient([], $options);
        $client->request('GET', '/');

        $this->assertSame('', $this->strategy->apply($client->getRequest()));
    }
}
