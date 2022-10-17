<?php
/**
 * value initializer
 */

namespace Graviton\RestBundle\RestListener;

use Graviton\CoreBundle\ValueInitializer\ValueInitializer;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValueInitializerListener extends RestListenerAbstract
{

    /**
     * @var array
     */
    private $initializers = [];

    /**
     * addInitializer
     *
     * @param string $fieldName       fieldname
     * @param string $initializerName name
     *
     * @return void
     */
    public function addInitializer(string $fieldName, string $initializerName)
    {
        $this->initializers[] = [
            'fieldName' => $fieldName,
            'initializer' => $initializerName
        ];
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
        if (empty($this->initializers)) {
            return $event;
        }

        $entity = $event->getEntity();

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->initializers as $initializer) {
            // get the value
            $existingValue = $accessor->getValue($entity, $initializer['fieldName']);

            // set the value
            $accessor->setValue(
                $entity,
                $initializer['fieldName'],
                ValueInitializer::getInitialValue(
                    $initializer['initializer'],
                    $existingValue
                )
            );
        }

        $event->setEntity($entity);

        return $event;
    }
}
