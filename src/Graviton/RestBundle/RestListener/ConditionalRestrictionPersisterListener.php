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
class ConditionalRestrictionPersisterListener extends RestListenerAbstract
{

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * set EntityName
     *
     * @param string $entityName entityName
     *
     * @return void
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * set FieldName
     *
     * @param string $fieldName fieldName
     *
     * @return void
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
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
