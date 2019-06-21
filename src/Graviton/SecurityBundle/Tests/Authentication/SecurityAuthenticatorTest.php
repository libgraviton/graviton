<?php
/**
 * SecurityAuthenticatorTest
 */

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Authentication\Provider\AuthenticationProvider;
use Graviton\SecurityBundle\Authentication\Provider\AuthenticationProviderDummy;
use Graviton\SecurityBundle\Authentication\Strategies\MultiStrategy;
use Graviton\SecurityBundle\Entities\AnonymousUser;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Graviton\SecurityBundle\Entities\SubnetUser;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class SecurityAuthenticatorTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SecurityAuthenticatorTest extends GravitonTestCase
{
    /** @var Logger logger */
    private $logger;

    /**
     * @var AuthenticationProvider
     */
    private $userProvider;

    /**
     * @return void
     */
    protected function setUp() : void
    {
        /** @var \Psr\Log\LoggerInterface logger */
        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
                             ->setMethods(array('warning', 'info'))
                             ->getMockForAbstractClass();

        $this->userProvider = new AuthenticationProviderDummy();
    }

    /**
     * Test all auth methods for Multi Authentication -> this basically tests all others basic functionality
     *
     * @return void
     */
    public function testMultiAccess()
    {
        $userName = 'testUsername';

        /**
         * First is Auth start and second once validated
         * @var PreAuthenticatedToken $authenticated
         * @var PreAuthenticatedToken $token
         */

        $multiStrategy = new MultiStrategy();
        $multiStrategy->setStrategies(
            $this->getContainer(),
            [
                'graviton.security.authentication.strategy.header',
                'graviton.security.authentication.strategy.cookie',
                'graviton.security.authentication.strategy.subnet'
            ]
        );

        $authenticator = new SecurityAuthenticator(
            false,
            true,
            $this->userProvider,
            $multiStrategy,
            $this->logger
        );

        // Test Header
        $request = new Request();
        $request->headers->set('x-graviton-user', $userName);

        $credentials = $authenticator->getCredentials($request);
        $this->assertEquals(
            [
                'user' => $userName,
                'roles' => [
                    SecurityUser::ROLE_USER
                ]
            ],
            $credentials
        );

        $user = $authenticator->getUser($credentials, $this->userProvider);
        $this->assertInstanceOf(\stdClass::class, $user->getUser());
        $this->assertEquals([SecurityUser::ROLE_USER, SecurityUser::ROLE_CONSULTANT], $user->getRoles());

        // With header, but unknown and allowing Anonymous
        $request = new Request();
        $request->headers->set('x-rest-token', 'unknown');

        $credentials = $authenticator->getCredentials($request);
        $this->assertEquals(
            [
                'user' => false,
                'roles' => [
                    SecurityUser::ROLE_USER
                ]
            ],
            $credentials
        );

        $user = $authenticator->getUser($credentials, $this->userProvider);
        $this->assertInstanceOf(AnonymousUser::class, $user->getUser());
        $this->assertEquals([SecurityUser::ROLE_USER, SecurityUser::ROLE_ANONYMOUS], $user->getRoles());

        // With cookie
        $request = new Request();
        $request->cookies->set('x-graviton-user', $userName);

        $credentials = $authenticator->getCredentials($request);
        $this->assertEquals(
            [
                'user' => $userName,
                'roles' => [
                    SecurityUser::ROLE_USER
                ]
            ],
            $credentials
        );

        $user = $authenticator->getUser($credentials, $this->userProvider);
        $this->assertInstanceOf(\stdClass::class, $user->getUser());
        $this->assertEquals([SecurityUser::ROLE_USER, SecurityUser::ROLE_CONSULTANT], $user->getRoles());

        // Test Header for Subnet
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '127.0.0.10']);
        $request->headers->set('x-graviton-auth', $userName);

        $credentials = $authenticator->getCredentials($request);
        $this->assertEquals(
            [
                'user' => $userName,
                'roles' => [
                    SecurityUser::ROLE_USER,
                    SecurityUser::ROLE_SUBNET
                ]
            ],
            $credentials
        );

        $user = $authenticator->getUser($credentials, $this->userProvider);
        $this->assertInstanceOf(SubnetUser::class, $user->getUser());
        $this->assertEquals([SecurityUser::ROLE_USER, SecurityUser::ROLE_SUBNET], $user->getRoles());
    }

    /**
     * Test without sending any auth
     *
     * @return void
     */
    public function testHeaderRequiredAccess()
    {
        $this->expectException(AuthenticationException::class);

        $authenticator = new SecurityAuthenticator(
            false,
            false,
            $this->userProvider,
            $this->getContainer()->get('graviton.security.authentication.strategy.header'),
            $this->logger
        );

        $request = new Request();

        $credentials = $authenticator->getCredentials($request);
        $this->assertEquals(
            [
                'user' => '',
                'roles' => [SecurityUser::ROLE_USER]
            ],
            $credentials
        );

        $authenticator->getUser($credentials, $this->userProvider);
    }
}
