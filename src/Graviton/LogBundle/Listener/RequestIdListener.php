<?php
/**
 * sets an id on the request
 */

namespace Graviton\LogBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RequestIdListener
{

    /**
     * @var string
     */
    public const ATTRIBUTE_NAME = 'requestId';

    /**
     * sets the request id
     *
     * @param RequestEvent $event event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->getRequest() instanceof Request) {
            $event->getRequest()->attributes->set(self::ATTRIBUTE_NAME, bin2hex(random_bytes(5)));
        }
    }
}
