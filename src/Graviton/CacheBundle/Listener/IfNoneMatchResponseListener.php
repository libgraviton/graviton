<?php

namespace Graviton\CacheBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding a IfNoneMatch header.
 *
 * @category GravitonCacheBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class IfNoneMatchResponseListener
{
    /**
     * add a IfNoneMatch header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $ifNoneMatch = $request->headers->get('if-none-match');
        $etag = $response->headers->get('ETag', 'empty');

        if ($ifNoneMatch === $etag) {
            $response->setStatusCode(304);
            $response->setContent('');
        }
    }
}
