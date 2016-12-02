<?php
/**
 * CollectionCache class file
 */

namespace Graviton\DocumentBundle\Tests\Service;

use Graviton\CoreBundle\Repository\AppRepository;
use Graviton\DocumentBundle\Service\CollectionCache;
use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\EventStatusBundle\Document\EventStatus;
use GravitonDyn\EventStatusBundle\Document\EventStatusStatus;

/**
 * ExtReferenceConverter test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class CollectionCacheTest extends RestTestCase
{
    /** @var  CollectionCache */
    private $cache;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        $this->cache = $this->getContainer()->get('graviton.document.service.collectioncache');
        $config = $this->getContainer()->getParameter('graviton.cache.collections');
        if (!array_key_exists('EventStatus', $config)) {
            $config['EventStatus'] = 10;
            $this->cache->setConfiguration($config);
        }
    }

    /**
     * private build key test Makes an id
     *
     * @return void
     */
    public function testbuildCacheKey()
    {
        $collection = 'App';
        $id = 'kjeGd-213-csd';
        $key = CollectionCache::BASE_KEY.'-'.preg_replace("/[^a-zA-Z0-9_-]+/", "-", $collection.'-'.$id);

        $method = $this->getPrivateClassMethod(get_class($this->cache), 'buildCacheKey');
        $result = $method->invokeArgs($this->cache, [$collection, $id]);

        $this->assertEquals($key, $result);
    }

    /**
     * Time it should be in cache and if so should happen
     *
     * @return void
     */
    public function testgetCollectionCacheTime()
    {
        $collection = 'EventStatus';

        $config = $this->getContainer()->getParameter('graviton.cache.collections');
        $method = $this->getPrivateClassMethod(get_class($this->cache), 'getCollectionCacheTime');
        $result = $method->invokeArgs($this->cache, [$collection]);

        $this->assertEquals($config['EventStatus'], $result);
    }

    /**
     * Cache testing
     *
     * @return void
     */
    public function testsetAndgetByRepository()
    {
        /** @var AppRepository $repository */
        $repository = $this->getContainer()->get('gravitondyn.eventstatus.repository.eventstatus');
        $document = new EventStatus();
        $document->setId('testing-cache');
        $document->setCreatedate(new \DateTime());
        $this->cache->setByRepository($repository, $document);

        $object = $this->cache->getByRepository($repository, 'testing-cache');
        $this->assertEquals($document->getId(), $object->getId());
        $this->assertEquals($document->getCreatedate(), $object->getCreatedate());
    }

    /**
     * Cache testing
     *
     * @return void
     */
    public function testsetAndgetByRepositoryFalse()
    {
        /** @var AppRepository $repository */
        $repository = $this->getContainer()->get('gravitondyn.eventstatus.repository.eventstatusstatus');
        $document = new EventStatusStatus();
        $document->setId('testing-cache-2');
        $shouldNotBeSet = $this->cache->setByRepository($repository, $document);

        $this->assertFalse($shouldNotBeSet, '');
    }

    public function testLocks()
    {
        /** @var AppRepository $repository */
        $repository = $this->getContainer()->get('gravitondyn.eventstatus.repository.eventstatus');

        $id = 'ocack-test';
        $this->cache->addUpdateLock($repository, $id, 0.4);
        $start = microtime(true);
        $shouldHaveBeenReleased = $start + 500;
        $this->cache->updateOperationCheck($repository, $id);
        $end = microtime(true);
        $this->assertTrue(($start < $end) && ($end < $shouldHaveBeenReleased));
    }
}
