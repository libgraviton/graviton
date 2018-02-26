<?php
/**
 * Defines a set of listeners to be subscribed to authentication events.
 */
namespace Graviton\SecurityBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AuthenticationLogger implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param LoggerInterface $logger Logs information somewhere
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Indicates what event is listened to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        );
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param AuthenticationFailureEvent $event Event triggering this callback.
     *
     * @return Response
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        /** @var \Symfony\Component\Security\Core\Exception\AuthenticationException $exception */
        $exception = $event->getAuthenticationException();

        $this->logger->warning(
            $exception->getMessageKey(),
            array(
                'data' => $exception->getMessageData(),
            )
        );
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param AuthenticationEvent $event Event triggering this callback.
     *
     * @return void
     */
    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        /** @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token */
        $token = $event->getAuthenticationToken();

        $this->logger->info(
            sprintf(
                'Entity (%s) was successfully recognized.',
                $token->getUsername()
            )
        );
    }
}
