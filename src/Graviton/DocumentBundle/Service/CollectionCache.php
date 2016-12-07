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

    /** Prefix cache key */
    const BASE_UPDATE_KEY = 'CollectionUpdate';

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
            return $result;
        }
        return false;
    }

    /**
     * @param Repository $repository DB Repository
     * @param string     $serialized Serialised Object document
     * @param string     $id         Object document identifier
     * @return bool
     */
    public function setByRepository(Repository $repository, $serialized, $id)
    {
        $collection = $repository->getClassMetadata()->collection;
        if (!$time = $this->getCollectionCacheTime($collection)) {
            return false;
        }
        $key = $this->buildCacheKey($collection, $id);

        return $this->cache->save($key, $serialized, $time);
    }

    /**
     * Will sleep until previous operation has finished but for max 10s
     * Loops by 1/4 second
     *
     * @param Repository $repository Model repository
     * @param string     $id         Object identifier
     *
     * @return void
     */
    public function updateOperationCheck(Repository $repository, $id)
    {
        $collection = $repository->getClassMetadata()->collection;
        $key = self::BASE_UPDATE_KEY.'-'.$this->buildCacheKey($collection, $id);

        while ($this->cache->fetch($key)) {
            usleep(250000);
        }
    }

    /**
     * Will add update lock
     *
     * @param Repository $repository Model repository
     * @param string     $id         Object identifier
     * @param integer    $maxTime    Set timeout for lock
     *
     * @return bool
     */
    public function addUpdateLock(Repository $repository, $id, $maxTime = 10)
    {
        $collection = $repository->getClassMetadata()->collection;
        $key = self::BASE_UPDATE_KEY.'-'.$this->buildCacheKey($collection, $id);

        return  $this->cache->save($key, true, $maxTime);
    }

    /**
     * Will remove lock if there was one.
     *
     * @param Repository $repository Model repository
     * @param string     $id         Object identifier
     *
     * @return void
     */
    public function releaseUpdateLock(Repository $repository, $id)
    {
        $collection = $repository->getClassMetadata()->collection;
        $baseKey = $this->buildCacheKey($collection, $id);
        $key = self::BASE_UPDATE_KEY.'-'.$baseKey;

        $this->cache->delete($key);

        $collection = $repository->getClassMetadata()->collection;
        if ($this->getCollectionCacheTime($collection)) {
            $this->cache->delete($baseKey);
        }
    }

    /**
     * Update cache if needed
     *
     * @param array $configuration configuration array
     * @return void
     */
    public function setConfiguration($configuration)
    {
        $this->config = $configuration;
    }
}
