<?php
/**
 * Listener that invalidates (= removes) matching stuff from the Schema cache when appropriate
 */

namespace Graviton\SchemaBundle\Listener;

use Doctrine\Common\Cache\CacheProvider;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Event\RestEvent;
use Graviton\RestBundle\Model\DocumentModel;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaCacheInvalidationResponseListener
{
    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheInvalidationMapKey;

    /**
     * Constructor
     *
     * @param CacheProvider $cache                   cache
     * @param string        $cacheNamespace          cache namespace
     * @param string        $cacheInvalidationMapKey cache key of invalidation map
     */
    public function __construct(CacheProvider $cache, $cacheNamespace, $cacheInvalidationMapKey)
    {
        $cache->setNamespace($cacheNamespace);
        $this->cache = $cache;
        $this->cacheInvalidationMapKey = $cacheInvalidationMapKey;
    }

    /**
     * Invalidates stuff if where are in the invalidation map
     *
     * @param RestEvent $event rest event
     *
     * @return void
     */
    public function onRestRequest(RestEvent $event)
    {
        if ($event->getRequest()->getMethod() == 'GET') {
            return;
        }

        $controller = $event->getController();

        if ($controller instanceof RestController &&
            $controller->getModel() instanceof DocumentModel &&
            $this->cache->contains($this->cacheInvalidationMapKey)
        ) {
            $className = $controller->getModel()->getEntityClass();
            $invalidationMap = $this->cache->fetch($this->cacheInvalidationMapKey);

            if (!isset($invalidationMap[$className])) {
                return;
            }

            foreach ($invalidationMap[$className] as $cacheKey) {
                $this->cache->delete($cacheKey);
            }

            unset($invalidationMap[$className]);

            $this->cache->save($this->cacheInvalidationMapKey, $invalidationMap);
        }
    }
}
