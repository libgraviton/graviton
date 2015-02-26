<?php
/**
 * auth interface for authing against an airlock key of some sorts
 */

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface;
use Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface as SimplePreAuthInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class AirlockAuthenticationKeyAuthenticator implements
    SimplePreAuthInterface,
    AuthenticationFailureHandlerInterface,
    AuthenticationSuccessHandlerInterface
{
    /**
     * @var \Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider
     */
    protected $userProvider;

    /**
     * @var \Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface
     */
    protected $extractionStrategy;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    /**
     * @param AirlockAuthenticationKeyUserProvider $userProvider       user provider to use
     * @param StrategyInterface                    $extractionStrategy auth strategy to use
     * @param \Psr\Log\LoggerInterface             $logger             logger to user for logging errors
     */
    public function __construct(
        AirlockAuthenticationKeyUserProvider $userProvider,
        StrategyInterface $extractionStrategy,
        LoggerInterface $logger
    ) {
        $this->userProvider = $userProvider;
        $this->extractionStrategy = $extractionStrategy;
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request     request to authenticate
     * @param string                                    $providerKey provider key to auth with
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        // look for an apikey query parameter
        $apiKey = $this->extractionStrategy->apply($request);

        return new PreAuthenticatedToken(
            'anon.',
            $apiKey,
            $providerKey
        );
    }

    /**
     * Tries to authenticate the provided token
     *
     * @param TokenInterface        $token        token to authenticate
     * @param UserProviderInterface $userProvider provider to auth against
     * @param string                $providerKey  key to auth with
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $apiKey = $token->getCredentials();
        $username = $this->userProvider->getUsernameForApiKey($apiKey);

        if (!$username) {
            throw new AuthenticationException(
                sprintf('Airlock authentication key "%s" could not be resolved.', $apiKey)
            );
        }

        $user = $this->userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles()
        );
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
        $this->logger->warning(
            $exception->getMessageKey(),
            array(
                'data' => $exception->getMessageData(),
            )
        );

        return new Response(
            $exception->getMessageKey(),
            Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED
        );
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request        $request Current request to be processed
     * @param TokenInterface $token   Current token containing the authentication information
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->logger->info(
            sprintf(
                'Contract (%s (%s)) was successfully recognized.',
                $token->getUsername(),
                $token->getUser()->getContractNumber()
            )
        );
    }
}
