<?php
/**
 * global restriction handler interface
 */
namespace Graviton\RestBundle\Restriction\Handler;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
abstract class GlobalHandlerAbstract
{

    /**
     * modifies stuff on insert
     *
     * @param \ArrayAccess $entity entity
     *
     * @return \ArrayAccess entity
     */
    public function restrictInsert(\ArrayAccess $entity)
    {
        return $entity;
    }
}
