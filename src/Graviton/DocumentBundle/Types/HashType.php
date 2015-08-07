<?php
/**
 * HashType class file
 */

namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\Hash;

/**
 * Hash type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashType extends Type
{
    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return object|null
     */
    public function convertToPHPValue($value)
    {
        return is_array($value) ? new Hash($value) : null;
    }

    /**
     * Closure to convert to PHP value
     *
     * @return string
     */
    public function closureToPHP()
    {
        return <<<'PHP'
$return = (is_array($value) ? new \ Graviton\DocumentBundle\Entity\Hash($value) : null);
PHP;
    }

    /**
     * Convert to DB value
     *
     * @param mixed $value PHP value
     * @return array
     */
    public function convertToDatabaseValue($value)
    {
        if (is_array($value)) {
            $return = (object) $value;
        } elseif ($value instanceof \ArrayObject) {
            $return = (object) $value->getArrayCopy();
        } elseif (is_object($value)) {
            $return = (object) get_object_vars($value);
        } else {
            $return = null;
        }

        return $return;
    }

    /**
     * Closure to convert to DB value
     *
     * @return string
     */
    public function closureToMongo()
    {
        return <<<'PHP'
if (is_array($value)) {
    $return = (object) $value;
} elseif ($value instanceof \ArrayObject) {
    $return = (object) $value->getArrayCopy();
} elseif (is_object($value)) {
    $return = (object) get_object_vars($value);
} else {
    $return = null;
}
PHP;
    }
}
