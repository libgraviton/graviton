<?php
/**
 * FilterResponseListener for adding a IfNoneMatch header.
 */

namespace Graviton\CacheBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding a IfNoneMatch header.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
            $response->setContent(null);
        }
    }
}
