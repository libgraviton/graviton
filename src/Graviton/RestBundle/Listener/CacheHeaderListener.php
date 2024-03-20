<?php
/**
 * CacheHeaderListener
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RestBundle\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class CacheHeaderListener
{

    /**
     * VarnishListener constructor.
     *
     * @param string $varnishHeaderName header name
     */
    public function __construct(private string $varnishHeaderName)
    {
    }

    /**
     * add caching related headers
     *
     * @param ResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        // varnish tagging
        $varnishTags = $event->getRequest()->attributes->get('varnishTags');
        if (is_array($varnishTags)) {
            $varnishTags = implode(' ', $varnishTags);
        }
        if (!empty($varnishTags)) {
            $event->getResponse()->headers->set(
                $this->varnishHeaderName,
                $varnishTags
            );
        }

        $response = $event->getResponse();
        $content = $response->getContent();

        if (!empty($content) && !$response instanceof StreamedResponse) {
            $eTag = sprintf('W/%s', sha1($content));

            // if-none-match header?
            $ifNoneMatch = $event->getRequest()->headers->get('if-none-match');

            if ($ifNoneMatch === $eTag) {
                $event->getResponse()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                $event->getResponse()->setContent('');
                return;
            }

            $response->headers->set('ETag', $eTag);
        }
    }
}
