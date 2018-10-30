<?php
/**
 * doctrine custom type to handle translation arrays
 */
namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;

/**
 * based on http://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/basic-mapping.html#custom-mapping-types
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatableArrayType extends Type
{
    /**
     * Convert DB value to PHP representation
     *
     * @param mixed $value Value to convert
     * @return array
     */
    public static function convertToPhp($value)
    {
        if (!is_array($value)) {
            return [];
        }

        $elements = [];
        foreach ($value as $translatable) {
            $elements[] = TranslatableType::convertToPhp($translatable);
        }

        return $elements;
    }

    /**
     * Convert PHP value to MongoDb representation
     *
     * @param mixed $value Value to convert
     * @return array
     */
    public static function convertToDb($value)
    {
        if (!is_array($value)) {
            return [];
        }

        $elements = [];
        foreach ($value as $translatable) {
            $elements[] = TranslatableType::convertToDb($translatable);
        }

        return $elements;
    }

    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return array
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
     * @return array
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
