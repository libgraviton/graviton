<?php
/**
 * abstract class for dynamic service rest listeners
 */

namespace Graviton\RestBundle\RestListener;

use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Listener\DynServiceRestListener;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class RestListenerAbstract
{

    /**
     * @var DynServiceRestListener
     */
    private $context;

    /**
     * get Context
     *
     * @return DynServiceRestListener Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * set Context
     *
     * @param DynServiceRestListener $context context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * called before the entity is persisted
     *
     * @param EntityPrePersistEvent $event event
     *
     * @return EntityPrePersistEvent event
     */
    public function prePersist(EntityPrePersistEvent $event)
    {
        return $event;
    }
}
