<?php
/**
 * SecurityAuthenticator
 */

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface;
use Graviton\SecurityBundle\Authentication\Provider\AuthenticationProvider;
use Graviton\SecurityBundle\Entities\AnonymousUser;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Graviton\SecurityBundle\Entities\SubnetUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class SecurityAuthenticator extends AbstractGuardAuthenticator
{

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string                 $securityTestUsername user for testing
     * @param boolean                $allowAnonymous       user provider to use
     * @param AuthenticationProvider $userProvider         user provider to use
     * @param StrategyInterface      $extractionStrategy   auth strategy to use
     * @param LoggerInterface        $logger               logger to user for logging errors
     */
    public function __construct(
        $securityTestUsername,
        $allowAnonymous,
        AuthenticationProvider $userProvider,
        StrategyInterface $extractionStrategy,
        LoggerInterface $logger
    ) {
        $this->testUsername       = $securityTestUsername;
        $this->allowAnonymous     = $allowAnonymous;
        $this->userProvider       = $userProvider;
        $this->extractionStrategy = $extractionStrategy;
        $this->logger = $logger;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request   original request
     * @param AuthenticationException $exception exception from auth attempt
     *
     * @return Response|null response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
            $exception->getMessageKey(),
            Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED
        );
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * @param Request                 $request       The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('Auth information required', 401);
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request request
     *
     * @return bool always true
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * @param Request $request request
     *
     * @return mixed Any non-null value
     *
     * @throws \UnexpectedValueException If null is returned
     */
    public function getCredentials(Request $request)
    {
        return [
            'user' => $this->extractionStrategy->apply($request),
            'roles' => $this->extractionStrategy->getRoles()
        ];
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed                 $credentials  credentials
     * @param UserProviderInterface $userProvider user provider
     *
     * @return UserInterface|null
     * @throws AuthenticationException
     *
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $this->checkCredentialsBasic($credentials);

        $user = false;
        $roles = $credentials['roles'];

        // subnet?
        if (in_array(SecurityUser::ROLE_SUBNET, $roles)) {
            $this->logger->info('Authentication, subnet based user');
            $user = new SubnetUser($credentials['user']);
        }

        if (!$user && !empty($credentials['user'])) {
            // user case
            $this->logger->info(sprintf('Authentication, loading user "%s".', $credentials['user']));
            $user = $userProvider->loadUserByUsername($credentials['user']);

            if ($user === false) {
                $this->logger->info(
                    sprintf(
                        'Authentication, user "%s" not found, will fall back to anonymous.',
                        $credentials['user']
                    )
                );
            } else {
                $roles[] = SecurityUser::ROLE_CONSULTANT;
            }
        }

        if ($user === false && empty($this->testUsername)) {
            // anonymous case
            $this->logger->info('Authentication, loading anonymous user.');

            $user = new AnonymousUser();
            $roles[] = SecurityUser::ROLE_ANONYMOUS;
        } elseif ($user === false && !empty($this->testUsername)) {
            // test username case
            $this->logger->info('Authentication, loading test user.');

            $user = $userProvider->loadUserByUsername($this->testUsername);
            $roles[] = SecurityUser::ROLE_TEST;
        }

        if ($user !== false) {
            return new SecurityUser($user, $roles);
        }

        $message = sprintf('Authentication key "%s" could not be resolved.', $credentials['user']);
        $this->logger->warning($message);
        throw new AuthenticationException($message);
    }

    /**
     * Returns true if the credentials are valid.
     *
     * @param mixed         $credentials credentials
     * @param UserInterface $user        user
     *
     * @return bool if it all checks out..
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $this->checkCredentialsBasic($credentials);
        return true;
    }

    /**
     * basic credential situation check
     *
     * @param array $credentials credentials
     *
     * @return void
     */
    private function checkCredentialsBasic($credentials)
    {
        if (empty($credentials['user']) && $this->allowAnonymous === false && $this->testUsername === false) {
            throw new AuthenticationException(
                'Anonymous access is disabled'
            );
        }
    }

    /**
     * Called when authentication executed and was successful!
     *
     * @param Request        $request     request
     * @param TokenInterface $token       token
     * @param string         $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * supports cookie based cookie auth?
     *
     * @return bool always false
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
