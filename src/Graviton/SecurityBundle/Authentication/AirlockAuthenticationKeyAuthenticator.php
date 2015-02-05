<?php

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface;
use Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Class AirlockApiKeyAuthenticator
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class AirlockAuthenticationKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
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
     * @param \Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider   $userProvider
     * @param \Graviton\SecurityBundle\Authentication\Strategies\StrategyInterface $extractionStrategy
     */
    public function __construct(
        AirlockAuthenticationKeyUserProvider $userProvider,
        StrategyInterface $extractionStrategy
    ) {
        $this->userProvider = $userProvider;
        $this->extractionStrategy = $extractionStrategy;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $providerKey
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
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface          $userProvider
     * @param  string                                                              $providerKey
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $apiKey = $token->getCredentials();
        $username = $this->userProvider->getUsernameForApiKey($apiKey);

        if (!$username) {
            throw new AuthenticationException(
                sprintf('Airlock authtication key "%s" could not be resolved.', $apiKey)
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
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param  string                                                              $providerKey
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
     * @param \Symfony\Component\HttpFoundation\Request                          $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo: log on failure!!

        return new Response(
            $exception->getMessageKey(),
            Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED
        );
    }
}
