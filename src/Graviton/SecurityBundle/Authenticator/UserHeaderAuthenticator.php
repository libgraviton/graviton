<?php

namespace Graviton\SecurityBundle\Authenticator;

use Graviton\SecurityBundle\Entities\AnonymousUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Class AnonymousUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class UserHeaderAuthenticator extends AbstractAuthenticator
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $headerName;

    /**
     * @var bool
     */
    private $allowAnonymous;

    /**
     * @param LoggerInterface $logger         logger
     * @param string          $headerName     header name
     * @param bool            $allowAnonymous allow anonymous
     */
    public function __construct(
        LoggerInterface $logger,
        string $headerName,
        bool $allowAnonymous
    ) {
        $this->headerName = $headerName;
        $this->logger = $logger;
        $this->allowAnonymous = $allowAnonymous;
    }

    /**
     * see if we support the request
     *
     * @param Request $request request
     *
     * @return bool|null true
     */
    public function supports(Request $request): ?bool
    {
        // this is set by any previous authenticators - as this will always return an anonymous user if allowed
        return !$request->attributes->get('authenticated', false);
    }

    /**
     * authenticate
     *
     * @param Request $request HTTP Request
     *
     * @return Passport passport
     */
    public function authenticate(Request $request): Passport
    {
        $username = null;
        if ($request->headers->has($this->headerName)) {
            $username = $request->headers->get($this->headerName);
            $this->logger->info(
                'Detected user header "'.$this->headerName.'" (value "'.$username.'")'
            );
        } else {
            $this->logger->info(
                'Falling back to anonymous user'
            );
        }

        if (!is_null($username)) {
            return new SelfValidatingPassport(new UserBadge($username));
        }

        if (!$this->allowAnonymous) {
            throw new CustomUserMessageAuthenticationException('Anonymous access is not allowed');
        }

        return new SelfValidatingPassport(new UserBadge(AnonymousUser::USERNAME));
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
}
