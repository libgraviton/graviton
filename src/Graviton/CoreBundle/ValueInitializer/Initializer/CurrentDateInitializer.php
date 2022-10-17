<?php
/**
 * current date initializer
 */

namespace Graviton\CoreBundle\ValueInitializer\Initializer;

use Graviton\CoreBundle\ValueInitializer\ValueInitializerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CurrentDateInitializer implements ValueInitializerInterface
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
        if ($presentValue === null) {
            return new \DateTime();
        }

        return $presentValue;
    }
}
