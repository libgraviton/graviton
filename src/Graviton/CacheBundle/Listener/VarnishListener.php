<?php
/**
 * FilterResponseListener for adding a ETag header.
 */

namespace Graviton\CacheBundle\Listener;

use Monolog\Logger;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * VarnishListener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VarnishListener
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $serverName;

    /**
     * @var string
     */
    private $headerName;

    /**
     * VarnishListener constructor.
     *
     * @param Logger $logger     logger
     * @param string $serverName server name
     * @param string $headerName header name
     */
    public function __construct(Logger $logger, $serverName, $headerName)
    {
        $this->logger = $logger;
        $this->serverName = $serverName;
        $this->headerName = $headerName;
    }

    /**
     * add a IfNoneMatch header to the response
     *
     * @param ResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (is_null($this->serverName) || !$event->getRequest()->attributes->has('varnishTags')) {
            return;
        }

        $tags = $event->getRequest()->attributes->get('varnishTags');
        if (is_array($tags)) {
            $tags = implode(' ', $tags);
        }

        // add tag
        $event->getResponse()->headers->set(
            $this->headerName,
            $tags,
            true
        );

        $this->logger->info('CACHESERVER LISTENER: TAGGING', [$tags]);
    }
}
