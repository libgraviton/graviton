<?php
/**
 * FilterResponseListener for adding a ETag header.
 */

namespace Graviton\CacheBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding a ETag header.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
