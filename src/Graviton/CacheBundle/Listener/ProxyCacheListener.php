<?php
/**
 * Created by PhpStorm.
 * User: dn
 * Date: 24.11.17
 * Time: 16:37
 */

namespace Graviton\CacheBundle\Listener;


use FOS\HttpCacheBundle\CacheManager;
use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProxyCacheListener
{

    /**
     * @var SymfonyResponseTagger
     */
    private $responseTagger;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    private $tagOnMethodes = [
        'GET',
        'OPTIONS',
        'HEAD'
    ];

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
     * add a IfNoneMatch header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {

        $routeName = $event->getRequest()->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);
        array_pop($routeParts);
        $tags = [implode('.', $routeParts)];

        if (in_array($event->getRequest()->getMethod(), $this->tagOnMethodes)) {
            $this->responseTagger->addTags(array_merge($this->baseTags, $tags));
        } else {
            // if something is done within i18n, we delete everything
            if ($routeParts[1] == 'i18n') {
                $tags[] = 'all';
            }

            $this->cacheManager->invalidateTags($tags);
        }
    }
}
