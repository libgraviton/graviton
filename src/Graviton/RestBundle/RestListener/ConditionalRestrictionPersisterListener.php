<?php
/**
 * abstract class for dynamic service rest listeners
 */

namespace Graviton\RestBundle\RestListener;

use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\SecurityBundle\Service\SecurityUtils;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ConditionalRestrictionPersisterListener extends RestListenerAbstract
{

    /**
     * @var bool
     */
    private $persistRestrictions;

    /**
     * @var SecurityUtils
     */
    private $securityUtils;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $localField;

    /**
     * @var string
     */
    private $compareField;

    /**
     * @var mixed
     */
    private $compareValue;

    /**
     * set PersistRestrictions
     *
     * @param bool $persistRestrictions persistRestrictions
     *
     * @return void
     */
    public function setPersistRestrictions($persistRestrictions)
    {
        $this->persistRestrictions = $persistRestrictions;
    }

    /**
     * set SecurityUtils
     *
     * @param SecurityUtils $securityUtils securityUtils
     *
     * @return void
     */
    public function setSecurityUtils($securityUtils)
    {
        $this->securityUtils = $securityUtils;
    }

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
     * set EntityName
     *
     * @param string $localField localField
     *
     * @return void
     */
    public function setLocalField($localField)
    {
        $this->localField = $localField;
    }

    /**
     * set RemoteField
     *
     * @param string $compareField compareField
     *
     * @return void
     */
    public function setCompareField($compareField)
    {
        $this->compareField = $compareField;
    }

    /**
     * set CompareValue
     *
     * @param mixed $compareValue compareValue
     *
     * @return void
     */
    public function setCompareValue($compareValue)
    {
        $this->compareValue = $compareValue;
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
        // no restrictions or conditional mode disabled?
        if (!$this->securityUtils->hasDataRestrictions() || $this->persistRestrictions) {
            return $event;
        }

        $entity = $event->getEntity();
        // can we lookup?
        if (!$entity instanceof \ArrayAccess ||
            !isset($entity[$this->localField]) ||
            is_null($entity[$this->localField])
        ) {
            return $event;
        }

        $relatedEntity = $this->getContext()->getDm()->find($this->entityName, $entity[$this->localField]);

        // can we check the property?
        if (!$relatedEntity instanceof \ArrayAccess ||
            !isset($relatedEntity[$this->compareField]) ||
            is_null($relatedEntity[$this->compareField])
        ) {
            return $event;
        }

        // set the value
        if ($relatedEntity[$this->compareField] == $this->compareValue) {
            foreach ($this->securityUtils->getRequestDataRestrictions() as $fieldName => $fieldValue) {
                $entity[$fieldName] = $fieldValue;
            }
            $event->setEntity($entity);
        }

        return $event;
    }
}
