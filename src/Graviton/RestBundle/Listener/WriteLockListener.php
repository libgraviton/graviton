<?php
/**
 * listener that implements write locks on data altering requests with PUT and PATCH methods
 * and adds random waits to certain operations.
 */
namespace Graviton\RestBundle\Listener;

use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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
     * @var CacheItemPoolInterface
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
     * @param Logger                 $logger         logger
     * @param RequestStack           $requestStack   request stack
     * @param CacheItemPoolInterface $cache          cache
     * @param array                  $randomWaitUrls urls we randomly wait on
     */
    public function __construct(
        Logger $logger,
        RequestStack $requestStack,
        CacheItemPoolInterface $cache,
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
     * @param ControllerEvent $event response listener event
     *
     * @return void
     */
    public function onKernelController(ControllerEvent $event)
    {
        $currentMethod = $this->requestStack->getCurrentRequest()->getMethod();

        // ignore not defined methods..
        if (!in_array($currentMethod, $this->interestedMethods)) {
            return;
        }

        $url = $this->requestStack->getCurrentRequest()->getPathInfo();
        $cacheKey = $this->cacheKeyPrefix.sha1($url);

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
        while ($this->cache->hasItem($cacheKey) === true) {
            usleep(250000);
        }

        $this->logger->info("LOCK CHECK FINISHED = ".$cacheKey);

        if (in_array($currentMethod, $this->waitingMethods)) {
            // current method just wants to wait..
            return;
        }

        // create new
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set(true);
        $cacheItem->expiresAfter($this->maxTime);

        $this->cache->save($cacheItem);
        $this->logger->info("LOCK ADD = ".$cacheKey);

        $event->getRequest()->attributes->set('writeLockOn', $cacheKey);
    }

    /**
     * release the lock
     *
     * @param ResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $this->releaseLock($event->getRequest());
    }

    /**
     * release the lock on exceptions
     *
     * @param ExceptionEvent $event event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $this->releaseLock($event->getRequest());
    }

    /**
     * releases the lock if needed
     *
     * @param Request $request request
     *
     * @return void
     */
    private function releaseLock(Request $request)
    {
        $lockName = $request->attributes->get('writeLockOn', null);
        if (!is_null($lockName)) {
            $this->cache->deleteItem($lockName);
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
