<?php
/**
 * FilterResponseListener for adding a ETag header.
 */

namespace Graviton\CacheBundle\Listener;

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
     * @var SymfonyResponseTagger
     */
    private $responseTagger;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * which methods should be tagged
     *
     * @var array
     */
    private $tagOnMethodes = [
        'GET',
        'OPTIONS',
        'HEAD'
    ];

    /**
     * tag that every item should receive
     *
     * @var array
     */
    private $baseTags = [
        'all'
    ];

    /**
     * set ResponseTagger
     *
     * @param SymfonyResponseTagger $responseTagger responseTagger
     *
     * @return void
     */
    public function setResponseTagger($responseTagger)
    {
        $this->responseTagger = $responseTagger;
    }

    /**
     * set CacheManager
     *
     * @param CacheManager $cacheManager cacheManager
     *
     * @return void
     */
    public function setCacheManager($cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * set logger
     *
     * @param Logger $logger logger
     *
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
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
        $method = $event->getRequest()->getMethod();
        $path = $this->normalizePath($event->getRequest()->getPathInfo());
        $routeParts = explode('/', $path);

        // do we have a base path?
        $basePath = null;
        if (count($routeParts) > 1) {
            array_pop($routeParts);
            $basePath = implode('/', $routeParts);
        }

        $baseTags = [$path];
        if ($event->getRequest()->attributes->has('varnishTags') &&
            is_array($event->getRequest()->attributes->get('varnishTags'))
        ) {
            $baseTags = array_merge($baseTags, $event->getRequest()->attributes->get('varnishTags'));
        }

        if (in_array($method, $this->tagOnMethodes)) {
            if (!is_null($basePath)) {
                $baseTags[] = $basePath;
            }
            $tags = array_merge($this->baseTags, $baseTags);

            $this->logger->info('CACHESERVER LISTENER: TAGGING', [$tags]);

            $this->responseTagger->addTags($tags);
        } else {
            // don't add basepath in case of POST as there is no element (<id>) part in url..
            if (!is_null($basePath) && $method != 'POST') {
                $baseTags[] = $basePath;
            }

            $this->logger->info('CACHESERVER LISTENER: INVALIDATING', [$baseTags]);

            $this->cacheManager->invalidateTags($baseTags);
        }
    }

    /**
     * make sure the path is as we expect it
     *
     * @param string $path path
     *
     * @return string path
     */
    private function normalizePath($path)
    {
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        };
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        };
        return $path;
    }
}
