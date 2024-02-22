<?php
/**
 * FilterResponseListener for adding a ETag header.
 */

namespace Graviton\CacheBundle\Listener;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * FilterResponseListener for adding a ETag header.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ETagResponseListener
{
    /**
     * add a ETag header to the response
     *
     * @param ResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();

        if ($response instanceof StreamedResponse || empty($response->getContent())) {
            return;
        }

        /**
         * the "W/" prefix is necessary to qualify it as a "weak" Etag.
         * only then a proxy like nginx will leave the tag alone because a strong cannot
         * match if gzip is applied.
         */
        $response->headers->set('ETag', 'W/'.sha1($response->getContent()));
    }
}
