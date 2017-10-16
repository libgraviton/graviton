<?php
/**
 * main checks for airlock authenticator
 */

namespace Graviton\SecurityBundle\Authentication;

use \Graviton\SecurityBundle\Authentication\Provider\AuthenticationProvider;
use \Graviton\SecurityBundle\Authentication\Provider\AuthenticationProviderDummy;
use \Graviton\SecurityBundle\Authentication\Strategies\CookieFieldStrategy;
use \Graviton\SecurityBundle\Authentication\Strategies\HeaderFieldStrategy;
use \Graviton\SecurityBundle\Authentication\Strategies\MultiStrategy;
use \Graviton\SecurityBundle\Authentication\Strategies\SameSubnetStrategy;
use \Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface;
use \Graviton\SecurityBundle\Entities\AnonymousUser;
use \Graviton\SecurityBundle\Entities\SecurityUser;
use \Graviton\SecurityBundle\Entities\SubnetUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Role\Role;
use \Psr\Log\LoggerInterface as Logger;

/**
 * Class AirlockAuthenticationKeyAuthenticatorTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityAuthenticatorTest extends TestCase
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
    protected function setUp()
    {
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject logger */
        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->setMethods(array('warning', 'info'))
            ->getMockForAbstractClass();

        $this->userProvider = new AuthenticationProviderDummy();
    }

    /**
     * Test all auth methods for Multi Authentication
     *
     * @covers MultiStrategy::addStrategy()
     * @covers SecurityAuthenticator::createToken()
     * @covers SecurityAuthenticator::authenticateToken()
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
        $multiStrategy->addStrategy($this->getStrategyByName('header'));
        $multiStrategy->addStrategy($this->getStrategyByName('cookie'));
        $multiStrategy->addStrategy($this->getStrategyByName('subnet'));

        $authenticator = new SecurityAuthenticator(
            true,
            false,
            true,
            $this->userProvider,
            $multiStrategy,
            $this->logger
        );

        // Test Header
        $request = new Request();
        $request->headers->set('x-rest-token', $userName);

        $token = $authenticator->createToken($request, 'test-key');
        $authenticated = $authenticator->authenticateToken($token, $this->userProvider, 'test-key');

        $roles = $this->rolesToArray($authenticated->getRoles());
        // Getting the dummy user and not the real Document User.
        $user = $authenticated->getUser()->getUser();
        $this->assertEquals([SecurityUser::ROLE_CONSULTANT, SecurityUser::ROLE_USER], $roles, json_encode($roles));
        $this->assertEquals($userName, $user->username);

        // With header, but unknown and allowing Anonymous
        $request = new Request();
        $request->headers->set('x-rest-token', 'unknown');

        $token = $authenticator->createToken($request, 'test-key');
        $authenticated = $authenticator->authenticateToken($token, $this->userProvider, 'test-key');

        $roles = $this->rolesToArray($authenticated->getRoles());
        /** @var AnonymousUser $user */
        $user = $authenticated->getUser()->getUser();
        $this->assertEquals([SecurityUser::ROLE_ANONYMOUS, SecurityUser::ROLE_USER], $roles, json_encode($roles));
        $this->assertEquals('anonymous', $user->getUsername());

        // With cookie
        $request = new Request();
        $request->cookies->set('graviton_user', $userName);

        $token = $authenticator->createToken($request, 'test-key');
        $authenticated = $authenticator->authenticateToken($token, $this->userProvider, 'test-key');

        $roles = $this->rolesToArray($authenticated->getRoles());
        /** @var \stdClass $user */
        $user = $authenticated->getUser()->getUser();
        $this->assertEquals([SecurityUser::ROLE_CONSULTANT, SecurityUser::ROLE_USER], $roles, json_encode($roles));
        $this->assertEquals($userName, $user->username);


        // Test Header for Subnet
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '0.0.0.0']);
        $request->headers->set('graviton_subnet', $userName);

        $token = $authenticator->createToken($request, 'test-key');
        $authenticated = $authenticator->authenticateToken($token, $this->userProvider, 'test-key');

        $roles = $this->rolesToArray($authenticated->getRoles());
        // Getting the dummy user and not the real Document User.
        /** @var SubnetUser $user */
        $user = $authenticated->getUser()->getUser();
        $this->assertEquals([SecurityUser::ROLE_SUBNET, SecurityUser::ROLE_USER], $roles, json_encode($roles));
        $this->assertEquals($userName, $user->getUsername());
    }

    /**
     * Test all auth methods for Multi Authentication
     *
     * @return void
     */
    public function testHeaderAccess()
    {
        $userName = 'testUsername';

        /**
         * First is Auth start and second once validated
         * @var PreAuthenticatedToken $authenticated
         * @var PreAuthenticatedToken $token
         */

        $strategy = $this->getStrategyByName('header');

        $authenticator = new SecurityAuthenticator(
            false,
            false,
            true,
            $this->userProvider,
            $strategy,
            $this->logger
        );

        // Test Header
        $request = new Request();
        $request->headers->set('x-rest-token', $userName);

        $token = $authenticator->createToken($request, 'test-key');
        $authenticated = $authenticator->authenticateToken($token, $this->userProvider, 'test-key');

        $roles = $this->rolesToArray($authenticated->getRoles());
        // Getting the dummy user and not the real Document User.
        $user = $authenticated->getUser()->getUser();
        $this->assertEquals([SecurityUser::ROLE_CONSULTANT, SecurityUser::ROLE_USER], $roles, json_encode($roles));
        $this->assertEquals($userName, $user->username);

        // With header, but unknown and allowing Anonymous
        $request = new Request();
        $request->headers->set('x-rest-token', 'unknown');

        $token = $authenticator->createToken($request, 'test-key');
        $authenticated = $authenticator->authenticateToken($token, $this->userProvider, 'test-key');

        $roles = $this->rolesToArray($authenticated->getRoles());
        /** @var AnonymousUser $user */
        $user = $authenticated->getUser()->getUser();
        $this->assertEquals([SecurityUser::ROLE_ANONYMOUS, SecurityUser::ROLE_USER], $roles, json_encode($roles));
        $this->assertEquals('anonymous', $user->getUsername());
    }

    /**
     * Test without sending any auth
     *
     * @return void
     */
    public function testHeaderRequiredAccess()
    {
        $this->expectException(AuthenticationException::class);

        /**
         * First is Auth start and second once validated
         * @var PreAuthenticatedToken $authenticated
         * @var PreAuthenticatedToken $token
         */

        $strategy = $this->getStrategyByName('header');

        $authenticator = new SecurityAuthenticator(
            true,
            false,
            true,
            $this->userProvider,
            $strategy,
            $this->logger
        );

        // With header, but unknown and allowing Anonymous
        $request = new Request();
        $token = $authenticator->createToken($request, 'test-key');
        $authenticator->authenticateToken($token, $this->userProvider, 'test-key');
    }

    /**
     * Flat out roles for easier comparison. Sorted.
     * @param Role[] $roles Array list of Sf Roles
     *
     * @return array
     */
    private function rolesToArray($roles)
    {
        $roles = array_map(
            function ($role) {
                /** @var Role $role */
                return (string) $role->getRole();
            },
            $roles
        );
        sort($roles);
        return $roles;
    }

    /**
     * Simplified Strategy getter
     *
     * @param string $strategy Name of requested strategy
     * @return StrategyInterface|null
     */
    private function getStrategyByName($strategy)
    {
        switch ($strategy) {
            case 'header':
                return new HeaderFieldStrategy('x-rest-token');
                break;
            case 'cookie':
                return new CookieFieldStrategy('graviton_user');
                break;
            case 'subnet':
                return new SameSubnetStrategy('0.0.0.0', 'graviton_subnet');
                break;
        }
        return null;
    }
}
