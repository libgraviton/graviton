<?php
/**
 * Created by PhpStorm.
 * User: dn
 * Date: 17.11.17
 * Time: 16:30
 */

namespace Graviton\RestBundle\Serializer;


use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;

class SerializationContextFactory extends ContextFactoryAbstract implements SerializationContextFactoryInterface
{

    /**
     * @return SerializationContext
     */
    public function createSerializationContext()
    {
        return $this->workOnInstance(SerializationContext::create());
    }
}
