<?php
/**
 * auth interface for authing against an airlock key of some sorts
 */

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface;
use Graviton\SecurityBundle\Authentication\Provider\AuthenticationProvider;
use Graviton\SecurityBundle\Entities\AnonymousUser;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Graviton\SecurityBundle\Entities\SubnetUser;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class SecurityAuthenticator implements
    SimplePreAuthenticatorInterface,
    AuthenticationFailureHandlerInterface
{

    /**
     * Authentication can be required to use any service
     * @var bool,
     */
    protected $securityRequired;

    /**
     * Authentication can use a test user if no user found
     * @var bool,
     */
    protected $testUsername;

    /**
     * Authentication can allow not identified users to get information
     * @var bool,
     */
    protected $allowAnonymous;

    /**
     * @var AuthenticationProvider
     */
    protected $userProvider;

    /**
     * @var StrategyInterface
     */
    protected $extractionStrategy;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @param boolean                $securityRequired     user provider to use
     * @param string                 $securityTestUsername user for testing
     * @param boolean                $allowAnonymous       user provider to use
     * @param AuthenticationProvider $userProvider         user provider to use
     * @param StrategyInterface      $extractionStrategy   auth strategy to use
     * @param Logger                 $logger               logger to user for logging errors
     */
    public function __construct(
        $securityRequired,
        $securityTestUsername,
        $allowAnonymous,
        AuthenticationProvider $userProvider,
        StrategyInterface $extractionStrategy,
        Logger $logger
    ) {

        $this->securityRequired   = $securityRequired;
        $this->testUsername       = $securityTestUsername;
        $this->allowAnonymous     = $allowAnonymous;
        $this->userProvider       = $userProvider;
        $this->extractionStrategy = $extractionStrategy;

        $this->logger = $logger;
    }

    /**
     * @param Request $request     request to authenticate
     * @param string  $providerKey provider key to auth with
     *
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        // look for an apikey query parameter
        $apiKey = $this->extractionStrategy->apply($request);

        $token = new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey,
            $this->extractionStrategy->getRoles()
        );

        $token->setAttribute('ipAddress', $request->getClientIp());

        return $token;
    }

    /**
     * Tries to authenticate the provided token
     *
     * @param TokenInterface        $token        token to authenticate
     * @param UserProviderInterface $userProvider provider to auth against
     * @param string                $providerKey  key to auth with
     *
     * @return PreAuthenticatedToken
     */
    public function authenticateToken(
        TokenInterface $token,
        UserProviderInterface $userProvider,
        $providerKey
    ) {
        $username = $token->getCredentials();
        $roles    = array_map(array($this, 'objectRolesToArray'), $token->getRoles());
        $user     = false;

        // If no username in Strategy, check if required.
        if ($this->securityRequired && !$username) {
            $this->logger->warning('Authentication key is required.');
            throw new AuthenticationException('Authentication key is required.');
        }

        if (in_array(SecurityUser::ROLE_SUBNET, $roles)) {
            $this->logger->info('Authentication, loaded subnet user IP address: '. $token->getAttribute('ipAddress'));
            $user = new SubnetUser($username);
        } elseif ($username && $user = $this->userProvider->loadUserByUsername($username)) {
            $roles[] = SecurityUser::ROLE_CONSULTANT;
        }

        // If no user, try to fetch the test user, else check if anonymous is enabled
        if (!$user) {
            if ($this->testUsername && $user = $this->userProvider->loadUserByUsername($this->testUsername)) {
                $this->logger->info('Authentication, test user: ' . $this->testUsername);
                $roles[] = SecurityUser::ROLE_TEST;
            } elseif ($this->allowAnonymous) {
                $this->logger->info('Authentication, loading anonymous user.');
                $user = new AnonymousUser();
                $roles[] = SecurityUser::ROLE_ANONYMOUS;
            }
        }

        /** @var SecurityUser $securityUser */
        if ($user) {
            $securityUser = new SecurityUser($user, $roles);
        } else {
            $this->logger->warning(sprintf('Authentication key "%s" could not be resolved.', $username));
            throw new AuthenticationException(
                sprintf('Authentication key "%s" could not be resolved.', $username)
            );
        }

        return new PreAuthenticatedToken(
            $securityUser,
            $username,
            $providerKey,
            $securityUser->getRoles()
        );
    }

    /**
     * Convert object role to string role.
     *
     * @param RoleInterface $role Object role
     * @return null|string
     */
    private function objectRolesToArray(RoleInterface $role)
    {
        return $role->getRole();
    }

    /**
     * @param TokenInterface $token       token to check
     * @param string         $providerKey provider to check against
     *
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request   original request
     * @param AuthenticationException $exception exception from auth attempt
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
            $exception->getMessageKey(),
            Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED
        );
    }
}
