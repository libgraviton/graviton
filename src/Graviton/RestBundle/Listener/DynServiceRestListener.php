<?php
/**
 * listener that wraps the the RestListenerAbstract that is specified
 * in the service definition of a service - this is then generated into a service using
 * this service class as the actual Listener class
 */

namespace Graviton\RestBundle\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Listener\DynService\RestListenerAbstract;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
     * @var ParameterBagInterface
     */
    private $parameters;

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
     * HttpHeader constructor.
     *
     * @param LoggerInterface       $logger       logger
     * @param ParameterBagInterface $parameterBag parameters
     * @param RequestStack          $requestStack request stack
     * @param DocumentManager       $dm           document manager
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag,
        RequestStack $requestStack,
        DocumentManager $dm
    ) {
        $this->logger = $logger;
        $this->parameters = $parameterBag;
        $this->requestStack = $requestStack;
        $this->dm = $dm;
    }

    /**
     * get Parameters
     *
     * @return ParameterBagInterface Parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * set Parameters
     *
     * @param ParameterBagInterface $parameters parameters
     *
     * @return void
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
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
     * gets called before we persist an entity
     *
     * @param EntityPrePersistEvent $event event
     *
     * @return EntityPrePersistEvent event
     */
    public function prePersist(EntityPrePersistEvent $event)
    {
        $this->listener->setContext($this);
        return $this->listener->prePersist($event);
    }
}
