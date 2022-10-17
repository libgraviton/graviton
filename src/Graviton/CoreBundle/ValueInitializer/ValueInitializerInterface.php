<?php
/**
 * ValueInitializerInterface
 */

namespace Graviton\CoreBundle\ValueInitializer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface ValueInitializerInterface
{
    /**
     * gets the value
     *
     * @param mixed $presentValue value
     *
     * @return mixed value
     */
    public function getInitialValue(mixed $presentValue): mixed;
}
