<?php
/**
 * Class RestSubscriber
 */

namespace Graviton\RestBundle\Subscriber;

use Graviton\LinkHeaderParser\LinkHeader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestSubscriber implements EventSubscriberInterface
{

    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

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
        $request = $event->getRequest();
        $response = $event->getResponse();

        // ensure json charset
        $contentType = $response->headers->get('content-type');
        if ($contentType == 'application/json') {
            $response->headers->set(
                'content-type',
                'application/json; charset=UTF-8'
            );
        }

        // record count header
        if ($request->attributes->has('recordCount')) {
            $response->headers->set(
                'X-Record-Count',
                (string) $request->attributes->get('recordCount')
            );
        }

        // search source header?
        if ($request->attributes->has('X-Search-Source')) {
            $response->headers->set(
                'X-Search-Source',
                (string) $request->attributes->get('X-Search-Source')
            );
        }
    }

    public function onException(ExceptionEvent $event): void
    {
        $hans = 3;
        // ...
    }
}
