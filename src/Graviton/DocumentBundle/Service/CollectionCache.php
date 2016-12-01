<?php
/**
 * ExtReferenceConverter class file
 */

namespace Graviton\DocumentBundle\Service;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ODM\MongoDB\DocumentRepository as Repository;

/**
 * graviton.cache.collections

 * Collection Cache Service
 *
 *
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CollectionCache
{
    /** Prefix cache key */
    const BASE_KEY = 'CollectionCache';

    /** @var array  */
    private $config = [];

    /** @var CacheProvider */
    private $cache;

    /**
     * CollectionCache constructor.
     * @param CacheProvider $cache         Cache provider
     * @param array         $configuration Collections to be saved
     */
    public function __construct(
        CacheProvider $cache,
        $configuration
    ) {
        $this->cache = $cache;
        $this->config = $configuration;
    }

    /**
     * Makes an id
     *
     * @param string $collection DB collection name
     * @param string $id         Object Identifier
     * @return string
     */
    private function buildCacheKey($collection, $id)
    {
        return self::BASE_KEY.'-'.preg_replace("/[^a-zA-Z0-9_-]+/", "-", $collection.'-'.$id);
    }

    /**
     * Time it should be in cache and if so should happen
     *
     * @param string $collection DB collection name
     * @return int
     */
    private function getCollectionCacheTime($collection)
    {
        if (array_key_exists($collection, $this->config)) {
            return (int) $this->config[$collection];
        }
        return 0;
    }

    /**
     * Return un cached object.
     *
     * @param Repository $repository DB Repository
     * @param string     $id         Queried is
     * @return object|false if no cache found
     */
    public function getByRepository(Repository $repository, $id)
    {
        $collection = $repository->getClassMetadata()->collection;
        if (!$this->getCollectionCacheTime($collection)) {
            return false;
        }
        $key = $this->buildCacheKey($collection, $id);

        if ($result = $this->cache->fetch($key)) {
            return unserialize($result);
        }
        return false;
    }

    /**
     * @param Repository $repository DB Repository
     * @param object     $document   Object document
     * @return bool
     */
    public function setByRepository(Repository $repository, $document)
    {
        if (empty($document)) {
            return false;
        }
        $collection = $repository->getClassMetadata()->collection;
        if (!$time = $this->getCollectionCacheTime($collection)) {
            return false;
        }
        $key = $this->buildCacheKey($collection, $document->getId());

        return $this->cache->save($key, serialize($document), $time);
    }
}
