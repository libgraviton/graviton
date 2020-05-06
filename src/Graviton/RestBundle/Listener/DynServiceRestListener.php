<?php
/**
 * listener that wraps the the RestListenerAbstract that is specified
 * in the service definition of a service - this is then generated into a service using
 * this service class as the actual Listener class
 */

namespace Graviton\RestBundle\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\RestBundle\RestListener\RestListenerAbstract;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DynServiceRestListener
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var RestListenerAbstract
     */
    private $listener;

    /**
     * @var string
     */
    private $entityName;

    /**
     * HttpHeader constructor.
     *
     * @param LoggerInterface $logger       logger
     * @param RequestStack    $requestStack request stack
     * @param DocumentManager $dm           document manager
     */
    public function __construct(
        LoggerInterface $logger,
        RequestStack $requestStack,
        DocumentManager $dm
    ) {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->dm = $dm;
    }

    /**
     * get RequestStack
     *
     * @return RequestStack RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * get Dm
     *
     * @return DocumentManager Dm
     */
    public function getDm()
    {
        return $this->dm;
    }

    /**
     * sets the actual listener
     *
     * @param RestListenerAbstract $listener listener
     *
     * @return void
     */
    public function setListenerClass(RestListenerAbstract $listener)
    {
        $this->listener = $listener;
    }

    /**
     * sets the entity class name for which this rest listener applies to
     *
     * @param string $entityName entity name
     *
     * @return void
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * called when data is being queried
     *
     * @param ModelQueryEvent $event event
     *
     * @return ModelQueryEvent event
     */
    public function onQuery(ModelQueryEvent $event)
    {
        // only call on class it applies to
        if ($event->getQueryBuilder() instanceof Builder &&
            $this->entityName == $event->getQueryBuilder()->getQuery()->getClass()->getName()
        ) {
            $this->listener->setContext($this);
            return $this->listener->onQuery($event);
        }
        return $event;
    }

    /**
     * gets called before we persist an entity
     *
     * @param EntityPrePersistEvent $event event
     *
     * @return EntityPrePersistEvent event
     */
    public function prePersist(EntityPrePersistEvent $event)
    {
        // only call on class it applies to
        if ($this->entityName == get_class($event->getEntity())) {
            $this->listener->setContext($this);
            return $this->listener->prePersist($event);
        }
        return $event;
    }
}
