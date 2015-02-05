<?php

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Class AirlockApiKeyAuthenticator
 *
 * @package Graviton\SecurityBundle\Authentication
 */
final class AirlockAuthenticationKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * Contains the mandatory authentication information.
     */
    const X_HEADER_FIELD = 'x-idp-usernameInhalt';

    /**
     * @var \Graviton\SecurityBundle\User\AirlockAuthenticationKeyUserProvider
     */
    protected $userProvider;


    /**
     * @param AirlockAuthenticationKeyUserProvider $userProvider
     */
    public function __construct(AirlockAuthenticationKeyUserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
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
        $apiKey = $this->extractFieldInfo($request->headers);

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
     * Extracts every mandatroy field from the request header.
     *
     * @param \Symfony\Component\HttpFoundation\HeaderBag $header object representation of the request header.
     *
     * @return string
     */
    private function extractFieldInfo(HeaderBag $header)
    {
        $this->validateField($header, self::X_HEADER_FIELD);

        return $header->get(self::X_HEADER_FIELD, '');
    }

    /**
     * Verifies that the provided header has the expected/mandatory fields.
     *
     * @param \Symfony\Component\HttpFoundation\HeaderBag $header    object representation of the request header.
     * @param string                                      $fieldName Name of the header field to be validated.
     *
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validateField(HeaderBag $header, $fieldName)
    {
        $passed = $header->has($fieldName);

        // get rid of anything not a valid character
        $authInfo = filter_var($header->get($fieldName), FILTER_SANITIZE_STRING);

        if (false !== $passed && !empty($authInfo)) {
            $passed = true;
        }

        // get rid of control characters
        if (false !== $passed && $authInfo === preg_replace('#[[:cntrl:]]#i', '', $authInfo)) {
            $passed = true;
        }

        if (false === $passed) {
            throw new HttpException(
                Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED,
                'Mandatory header field (' . $fieldName . ') not provided or invalid.'
            );
        }
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
            'Mandatory header field (' . self::X_HEADER_FIELD . ') not provided.',
            Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED
        );
    }
}
