<?php
/**
 * doctrine custom type to handle translations
 */
namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\Translatable;

/**
 * based on http://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/basic-mapping.html#custom-mapping-types
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatableType extends Type
{
    /**
     * Convert DB value to PHP representation
     *
     * @param mixed $value Value to convert
     * @return Translatable|null
     */
    public static function convertToPhp($value)
    {
        if (is_string($value)) {
            return Translatable::createFromOriginalString($value);
        }

        if (is_array($value)) {
            return Translatable::createFromTranslations($value);
        }

        return null;
    }

    /**
     * Convert PHP value to MongoDb representation
     *
     * @param mixed $value Value to convert
     * @return array|null
     */
    public static function convertToDb($value)
    {
        return $value instanceof Translatable ?
            $value->getTranslations() :
            null;
    }

    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return Translatable|null
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
    public function closureToPHP() : string
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
    public function closureToMongo() : string
    {
        return '$return = \\'.static::class.'::convertToDb($value);';
    }
}
