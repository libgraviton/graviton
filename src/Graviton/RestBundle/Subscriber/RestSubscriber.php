<?php
/**
 * Class RestSubscriber
 */

namespace Graviton\RestBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestSubscriber implements EventSubscriberInterface
{

    #[\Override] public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::REQUEST => [
                ['onRequest', 0]
            ],
            KernelEvents::RESPONSE => [
                ['onResponse', 0]
            ],
            KernelEvents::EXCEPTION => [
                ['onException', 0]
            ],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        // fill content-type if not set; to make it better for older clients
        $contentType = $event->getRequest()->headers->get('content-type');
        if (empty($contentType)) {
            $event->getRequest()->headers->set('content-type', 'application/json');
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        // ensure json charset
        $contentType = $event->getResponse()->headers->get('content-type');
        if ($contentType == 'application/json') {
            $event->getResponse()->headers->set(
                'content-type',
                'application/json; charset=UTF-8'
            );
        }


        // ...
    }

    public function onException(ExceptionEvent $event): void
    {
        $hans = 3;
        // ...
    }
}
