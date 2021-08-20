<?php

namespace Graviton\SecurityBundle\Authenticator;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Class AnonymousUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SameSubnetAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    /**
     * @var string
     */
    private $subnet;

    /**
     * @var bool
     */
    private $headerField;

    /**
     * @param string $subnet      Subnet to be checked (e.g. 10.2.0.0/24)
     * @param string $headerField Http header field to be searched for the 'username'
     */
    public function __construct($subnet, $headerField = 'x-graviton-authentication')
    {
        $this->subnet = $subnet;
        $this->headerField = $headerField;
    }

    /**
     * see if we support the request
     *
     * @param Request $request req
     *
     * @return bool|null true or false
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has($this->headerField);
    }

    /**
     * authenticate
     *
     * @param Request $request HTTP Request
     *
     * @return PassportInterface passport
     */
    public function authenticate(Request $request): PassportInterface
    {
        if (!IpUtils::checkIp($request->getClientIp(), $this->subnet)) {
            throw new CustomUserMessageAuthenticationException('User not allowed for subnet auth');
        }

        $request->attributes->set('authenticated', true);

        return new SelfValidatingPassport(
            new UserBadge($request->headers->get($this->headerField))
        );
    }

    /**
     * on success
     *
     * @param Request        $request      request
     * @param TokenInterface $token        token
     * @param string         $firewallName firewall name
     *
     * @return Response|null response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * on failure
     *
     * @param Request                 $request   request
     * @param AuthenticationException $exception exp
     *
     * @return Response|null response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Access forbidden', Response::HTTP_FORBIDDEN);
    }

    /**
     * start
     *
     * @param Request                      $request       request
     * @param AuthenticationException|null $authException exp
     *
     * @return Response|void response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
    }
}
