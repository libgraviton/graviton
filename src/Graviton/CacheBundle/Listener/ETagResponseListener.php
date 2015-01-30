<?php

namespace Graviton\CacheBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding a ETag header.
 *
 * @category GravitonCacheBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class ETagResponseListener
{
    /**
     * add a ETag header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        $response->headers->set('ETag', sha1($response->getContent()));
    }
}
