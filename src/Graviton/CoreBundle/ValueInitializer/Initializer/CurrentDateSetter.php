<?php
/**
 * current date setter
 */

namespace Graviton\CoreBundle\ValueInitializer\Initializer;

use Graviton\CoreBundle\ValueInitializer\ValueInitializerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CurrentDateSetter implements ValueInitializerInterface
{
    /**
     * gets the value
     *
     * @param mixed $presentValue value
     *
     * @return mixed value
     */
    public function getInitialValue(mixed $presentValue) : mixed
    {
        return new \DateTime();
    }
}
