<?php
/**
 * doctrine custom type to handle reading and writing $refs attributes
 */

namespace Graviton\DocumentBundle\Types;

use Graviton\DocumentBundle\Entity\ExtReference;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * based on http://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/basic-mapping.html#custom-mapping-types
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceType extends Type
{
    /**
     * Convert DB value to PHP representation
     *
     * @param mixed $value Value to convert
     * @return ExtReference|null
     */
    public static function convertToPhp($value)
    {
        if (is_array($value) && isset($value['$ref'], $value['$id'])) {
            return ExtReference::create($value['$ref'], $value['$id']);
        } elseif (is_object($value) && isset($value->{'$ref'}, $value->{'$id'})) {
            return ExtReference::create($value->{'$ref'}, $value->{'$id'});
        } else {
            return null;
        }
    }

    /**
     * Convert PHP value to MongoDb representation
     *
     * @param mixed $value Value to convert
     * @return array|null
     */
    public static function convertToDb($value)
    {
        if ($value instanceof ExtReference) {
            return ['$ref' => $value->getRef(), '$id' => $value->getId()];
        }

        return $value;
    }

    /**
     * Convert to PHP value
     *
     * @param mixed $value Db value
     * @return ExtReference|null
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
