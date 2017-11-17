<?php
/**
 * Created by PhpStorm.
 * User: dn
 * Date: 17.11.17
 * Time: 16:52
 */

namespace Graviton\RestBundle\Serializer;


use JMS\Serializer\Context;

abstract class ContextFactoryAbstract
{

    private $setSerializeNull = true;

    private $groups = ['Default'];

    /**
     * set SetSerializeNull
     *
     * @param bool $setSerializeNull setSerializeNull
     *
     * @return void
     */
    public function setSetSerializeNull($setSerializeNull)
    {
        $this->setSerializeNull = $setSerializeNull;
    }

    /**
     * set Groups
     *
     * @param array $groups groups
     *
     * @return void
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    protected function workOnInstance(Context $context)
    {
        $context = $context->setSerializeNull($this->setSerializeNull);

        if (is_array($this->groups) && !empty($this->groups)) {
            $context = $context->setGroups($this->groups);
        }

        return $context;
    }

}
