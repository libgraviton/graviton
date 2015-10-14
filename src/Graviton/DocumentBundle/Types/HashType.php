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
     * Convert DB value to PHP representation
     *
     * @param mixed $value Value to convert
     * @return Hash|null
     */
    public static function convertToPhp($value)
    {
        return is_array($value) ? new Hash($value) : null;
    }

    /**
     * Convert PHP value to MongoDb representation
     *
     * @param mixed $value Value to convert
     * @return object|null
     */
    public static function convertToDb($value)
    {
        if (is_array($value)) {
            return (object) $value;
        } elseif ($value instanceof \ArrayObject) {
            return (object) $value->getArrayCopy();
        } elseif (is_object($value)) {
            return (object) get_object_vars($value);
        } else {
            return null;
        }
    }

    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return Hash|null
     */
    public function convertToPHPValue($value)
    {
        return static::convertToPhp($value);
    }

    /**
     * Closure to convert to PHP value
     *
     * @return string
     */
    public function closureToPHP()
    {
        return '$return = \\'.static::class.'::convertToPhp($value);';
    }

    /**
     * Convert to DB value
     *
     * @param mixed $value PHP value
     * @return object|null
     */
    public function convertToDatabaseValue($value)
    {
        return static::convertToDb($value);
    }

    /**
     * Closure to convert to DB value
     *
     * @return string
     */
    public function closureToMongo()
    {
        return '$return = \\'.static::class.'::convertToDb($value);';
    }
}
