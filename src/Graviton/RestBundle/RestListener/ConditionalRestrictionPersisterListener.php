<?php
/**
 * abstract class for dynamic service rest listeners
 */

namespace Graviton\RestBundle\RestListener;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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
     * @var array
     */
    private $restrictionPersistMap;

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
    private $localFieldArrayMatcherExpression;

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
     * set RestrictionPersistMap
     *
     * @param array $restrictionPersistMap restrictionPersistMap
     *
     * @return void
     */
    public function setRestrictionPersistMap($restrictionPersistMap)
    {
        $this->restrictionPersistMap = $restrictionPersistMap;
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
     * symfony expression language expression that returns the value we to compare against
     *
     * @param string $localFieldArrayMatcherExpression expression
     *
     * @return void
     */
    public function setLocalFieldArrayMatcherExpression(string $localFieldArrayMatcherExpression): void
    {
        $this->localFieldArrayMatcherExpression = $localFieldArrayMatcherExpression;
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

        $relatedEntityId = null;

        // is $localField in array?
        if (is_array($entity[$this->localField])) {
            if (is_null($this->localFieldArrayMatcherExpression)) {
                throw new \RuntimeException(
                    self::class.': localField "'.$this->localField.'" ".
                    "is an array but there is no matcherExpression defined.'
                );
            }

            $expression = new ExpressionLanguage();
            foreach ($entity[$this->localField] as $entry) {
                $val = $expression->evaluate($this->localFieldArrayMatcherExpression, ['entry' => $entry]);
                if ($val !== false && !is_null($val)) {
                    // we found a value
                    $relatedEntityId = $val;
                    continue;
                }
            }

            // did not find applicable condition
            if (empty($relatedEntityId)) {
                return $event;
            }
        } else {
            $relatedEntityId = $entity[$this->localField];
        }

        // extrefence?
        if ($relatedEntityId instanceof ExtReference) {
            $relatedEntityId = $relatedEntityId->getId();
        }

        $relatedEntity = $this->getContext()->getDm()->find($this->entityName, $relatedEntityId);

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
                $fixedValue = $this->getFixedPersistValue($fieldName);
                if (is_null($fixedValue)) {
                    $entity[$fieldName] = $fieldValue;
                } else {
                    $entity[$fieldName] = $fixedValue;
                }
            }
        } else {
            foreach ($this->securityUtils->getRequestDataRestrictions() as $fieldName => $fieldValue) {
                unset($entity[$fieldName]);
            }
        }

        $event->setEntity($entity);

        return $event;
    }

    /**
     * gets a preconfigured value for a certain field to persist
     *
     * @param string $fieldName field name
     *
     * @return mixed|null value
     */
    private function getFixedPersistValue($fieldName)
    {
        if (!isset($this->restrictionPersistMap[$fieldName])) {
            return null;
        }

        $persistMapEntry = $this->restrictionPersistMap[$fieldName];
        $persistValue = $persistMapEntry['name'];
        if ($persistMapEntry['type'] == 'int') {
            $persistValue = (int) $persistValue;
        }

        return $persistValue;
    }
}
