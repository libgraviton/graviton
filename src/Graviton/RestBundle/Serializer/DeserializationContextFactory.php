<?php
/**
 * Created by PhpStorm.
 * User: dn
 * Date: 17.11.17
 * Time: 16:30
 */

namespace Graviton\RestBundle\Serializer;


use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;

class DeserializationContextFactory extends ContextFactoryAbstract implements DeserializationContextFactoryInterface
{


    /**
     * @return DeserializationContext
     */
    public function createDeserializationContext()
    {
        return $this->workOnInstance(DeserializationContext::create());
    }
}
