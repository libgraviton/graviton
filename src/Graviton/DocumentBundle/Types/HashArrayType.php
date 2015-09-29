<?php
/**
 * HashArrayType class file
 */

namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\Hash;

/**
 * Hash array type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashArrayType extends Type
{
    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return array
     */
    public function convertToPHPValue($value)
    {
        $return = array_map(
            function (array $value) {
                return new Hash($value);
            },
            is_array($value) ? array_values(array_filter($value, 'is_array')) : []
        );

        return $return;
    }

    /**
     * Closure to convert to PHP value
     *
     * @return string
     */
    public function closureToPHP()
    {
        return <<<'PHP'
$return = array_map(
    function (array $value) {
        return new \Graviton\DocumentBundle\Entity\Hash($value);
    },
    is_array($value) ? array_values(array_filter($value, 'is_array')) : []
);
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
        $return = array_map(
            function ($value) {
                if (is_array($value)) {
                    return (object) $value;
                } elseif ($value instanceof \ArrayObject) {
                    return (object) $value->getArrayCopy();
                } elseif (is_object($value)) {
                    return (object) get_object_vars($value);
                } else {
                    return (object) [];
                }
            },
            is_array($value) ? array_values($value) : []
        );

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
$return = array_map(
    function ($value) {
        if (is_array($value)) {
            return (object) $value;
        } elseif ($value instanceof \ArrayObject) {
            return (object) $value->getArrayCopy();
        } elseif (is_object($value)) {
            return (object) get_object_vars($value);
        } else {
            return (object) [];
        }
    },
    is_array($value) ? array_values($value) : []
);
PHP;
    }
}
