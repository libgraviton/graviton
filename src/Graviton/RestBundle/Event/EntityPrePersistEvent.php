<?php
/**
 * event that fires before we persist an entity in rest context
 */

namespace Graviton\RestBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EntityPrePersistEvent extends Event
{

    /**
     * @var string
     */
    public const NAME = 'document.model.event.entity.pre_persist';

    /**
     * @var object
     */
    private $entity;

    /**
     * gets entity
     *
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * set entity
     *
     * @param object $entity
     *
     * @return void
     */
    public function setEntity(object $entity): void
    {
        $this->entity = $entity;
    }
}
