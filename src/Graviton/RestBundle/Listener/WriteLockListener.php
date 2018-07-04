<?php
/**
 * listener that implements write locks on data altering requests with PUT and PATCH methods
 * and adds random waits to certain operations.
 */
namespace Graviton\RestBundle\Listener;

use Doctrine\Common\Cache\CacheProvider;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WriteLockListener
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * on these methods, we will create a lock and wait
     *
     * @var array
     */
    private $lockingMethods = [
        Request::METHOD_PUT,
        Request::METHOD_PATCH
    ];

    /**
     * on these methods, we will just wait..
     *
     * @var array
     */
    private $waitingMethods = [
        Request::METHOD_GET,
        Request::METHOD_DELETE
    ];

    /**
     * all methods we are interested in
     *
     * @var array
     */
    private $interestedMethods = [];

    /**
     * on these urls, we make a randomwait to randomly delay multiple incoming requests
     *
     * @var array
     */
    private $randomWaitUrls = [];

    /**
     * @var string
     */
    private $cacheKeyPrefix = 'writeLock-';

    /**
     * @var int
     */
    private $maxTime = 10;

    /**
     * @var int minimal delay in milliseconds
     */
    private $randomDelayMin = 200;

    /**
     * @var int maximal delay in milliseconds
     */
    private $randomDelayMax = 500;

    /**
     * @param Logger        $logger         logger
     * @param RequestStack  $requestStack   request stack
     * @param CacheProvider $cache          cache
     * @param array         $randomWaitUrls urls we randomly wait on
     */
    public function __construct(
        Logger $logger,
        RequestStack $requestStack,
        CacheProvider $cache,
        array $randomWaitUrls
    ) {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->cache = $cache;
        $this->interestedMethods = array_merge($this->lockingMethods, $this->waitingMethods);
        $this->randomWaitUrls = $randomWaitUrls;
    }

    /**
     * all "waiting" methods wait until no lock is around.. "writelock" methods wait and create a lock
     *
     * @param FilterControllerEvent $event response listener event
     *
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $currentMethod = $this->requestStack->getCurrentRequest()->getMethod();

        // ignore not defined methods..
        if (!in_array($currentMethod, $this->interestedMethods)) {
            return;
        }

        $url = $this->requestStack->getCurrentRequest()->getPathInfo();
        $cacheKey = $this->cacheKeyPrefix.$url;

        // should we do a random delay here? only applies to writing methods!
        if (in_array($currentMethod, $this->lockingMethods) &&
            $this->doRandomDelay($url)
        ) {
            $delay = rand($this->randomDelayMin, $this->randomDelayMax) * 1000;
            $this->logger->info("LOCK CHECK DELAY BY ".$delay." = ".$cacheKey);

            usleep(rand($this->randomDelayMin, $this->randomDelayMax) * 1000);
        }

        $this->logger->info("LOCK CHECK START = ".$cacheKey);

        // check for existing one
        while ($this->cache->fetch($cacheKey) === true) {
            usleep(250000);
        }

        $this->logger->info("LOCK CHECK FINISHED = ".$cacheKey);

        if (in_array($currentMethod, $this->waitingMethods)) {
            // current method just wants to wait..
            return;
        }

        // create new
        $this->cache->save($cacheKey, true, $this->maxTime);
        $this->logger->info("LOCK ADD = ".$cacheKey);

        $event->getRequest()->attributes->set('writeLockOn', $cacheKey);
    }

    /**
     * release the lock
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $lockName = $event->getRequest()->attributes->get('writeLockOn', null);
        if (!is_null($lockName)) {
            $this->cache->delete($lockName);
            $this->logger->info("LOCK REMOVED = ".$lockName);
        }
    }

    /**
     * if we should randomly wait on current request
     *
     * @param string $url the current url
     *
     * @return boolean true if yes, false otherwise
     */
    private function doRandomDelay($url)
    {
        return array_reduce(
            $this->randomWaitUrls,
            function ($carry, $value) use ($url) {
                if ($carry !== true) {
                    return (strpos($url, $value) === 0);
                } else {
                    return $carry;
                }
            }
        );
    }
}
