<?php
/**
 * HashType class file
 */

namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;

/**
 * Hash type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashType extends Type
{

    /**
     * extref converter
     *
     * @var ExtReferenceConverterInterface
     */
    private static $extRefConverter;

    /**
     * sets the converter
     *
     * @param ExtReferenceConverterInterface $converter converter
     *
     * @return void
     */
    public function setExtRefConverter(ExtReferenceConverterInterface $converter)
    {
        self::$extRefConverter = $converter;
    }

    /**
     * Convert DB value to PHP representation
     *
     * @param mixed $value Value to convert
     * @return Hash|null
     */
    public static function convertToPhp($value)
    {
        return is_array($value) ? new Hash(self::processDynamicParts($value)) : null;
    }

    /**
     * Convert PHP value to MongoDb representation
     *
     * @param mixed $value Value to convert
     * @return object|null
     */
    public static function convertToDb($value)
    {
        $dbValue = null;

        if (is_array($value)) {
            $dbValue = (object) $value;
        } elseif ($value instanceof \ArrayObject) {
            $dbValue = (object) $value->getArrayCopy();
        } elseif (is_object($value)) {
            $dbValue = (object) get_object_vars($value);
        }

        if (!is_null($dbValue)) {
            $dbValue = (object) self::processDynamicParts($dbValue);
        }

        return $dbValue;
    }

    /**
     * loops our structure recursively to
     * - find all $ref objects that need to be converted either from that or to that..
     * - empty objects that need to be marked accordingly
     *
     * @param mixed $input input structure
     *
     * @return array altered structure with replaced $ref objects
     */
    public static function processDynamicParts($input)
    {
        if ($input instanceof \stdClass) {
            if (!empty(get_object_vars($input))) {
                $input = self::processDynamicParts(get_object_vars($input));
            }
            return $input;
        }

        // extrefs
        $externalRefFieldName = '$ref';
        $internalRefFieldName = 'ref';

        // empty objects
        $emptyObjectToPhpValue = '_____EMPTY_PHP_OBJECT_____';

        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if ($key === $internalRefFieldName) {
                    if (is_array($value) && isset($value['$ref']) && isset($value['$id'])) {
                        $extRef = ExtReference::create($value['$ref'], $value['$id']);
                        $input[$externalRefFieldName] = self::$extRefConverter->getUrl($extRef);
                        unset($input[$internalRefFieldName]);
                    }
                } elseif ($key === $externalRefFieldName) {
                    $extRef = self::$extRefConverter->getExtReference($value);
                    $input[$internalRefFieldName] = $extRef->jsonSerialize();
                    unset($input[$externalRefFieldName]);
                } elseif ($value === $emptyObjectToPhpValue) {
                    $input[$key] = new \stdClass();
                } elseif (is_object($value) && empty((array) $value)) {
                    $input[$key] = $emptyObjectToPhpValue;
                } else {
                    if (is_array($value) || is_object($value)) {
                        $value = self::processDynamicParts($value);
                    }
                    $input[$key] = $value;
                }
            }
        }

        return $input;
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
