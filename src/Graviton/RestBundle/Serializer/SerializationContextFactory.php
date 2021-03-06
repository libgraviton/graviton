<?php
/**
 * serializer context factory
 */

namespace Graviton\RestBundle\Serializer;

use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SerializationContextFactory extends ContextFactoryAbstract implements SerializationContextFactoryInterface
{

    /**
     * creates the correct context
     *
     * @return SerializationContext
     */
    public function createSerializationContext(): SerializationContext
    {
        return $this->workOnInstance(SerializationContext::create());
    }
}
