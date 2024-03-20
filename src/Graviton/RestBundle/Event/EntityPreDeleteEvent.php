<?php
/**
 * event that fires before we delete an entity in rest context
 */

namespace Graviton\RestBundle\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EntityPreDeleteEvent extends EntityPrePersistEvent
{

    /**
     * @var string
     */
    const string NAME = 'document.model.event.entity.pre_delete';
}
