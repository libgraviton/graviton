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
     * Convert DB value to PHP representation
     *
     * @param mixed $value Value to convert
     * @return Hash[]
     */
    public static function convertToPhp($value)
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map([HashType::class, 'convertToPhp'], $value)));
    }

    /**
     * Convert PHP value to MongoDb representation
     *
     * @param mixed $value Value to convert
     * @return object[]
     */
    public static function convertToDb($value)
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map([HashType::class, 'convertToDb'], $value)));
    }

    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return Hash[]
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
     * @return object[]
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
