<?php
/**
 * doctrine custom type to handle reading and writing $refs attributes
 */

namespace Graviton\DocumentBundle\Types;

use Graviton\DocumentBundle\Entity\ExtReference as ExtRef;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * based on http://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/basic-mapping.html#custom-mapping-types
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceType extends Type
{
    /**
     * get php value when field is used as identifier
     *
     * @param mixed $value ref from mongodb
     * @return string
     */
    public function convertToPHPValue($value)
    {
        if (is_array($value) && isset($value['$ref'], $value['$id'])) {
            return ExtRef::create($value['$ref'], $value['$id']);
        } elseif (is_object($value) && isset($value->{'$ref'}, $value->{'$id'})) {
            return ExtRef::create($value->{'$ref'}, $value->{'$id'});
        } else {
            return null;
        }
    }

    /**
     * return a closure as string that sets $return if field is a regular field
     *
     * @return string
     */
    public function closureToPHP()
    {
        return <<<'PHP'
if (is_array($value) && isset($value['$ref'], $value['$id'])) {
    $return = \Graviton\DocumentBundle\Entity\ExtReference::create($value['$ref'], $value['$id']);
} elseif (is_object($value) && isset($value->{'$ref'}, $value->{'$id'})) {
    $return = \Graviton\DocumentBundle\Entity\ExtReference::create($value->{'$ref'}, $value->{'$id'});
} else {
    $return = null;
}
PHP;
    }

    /**
     * return the mongodb representation from a php value
     *
     * @param ExtRef $value Extreference
     *
     * @return array
     */
    public function convertToDatabaseValue($value)
    {
        return $value instanceof ExtRef ?
            \MongoDBRef::create($value->getRef(), $value->getId()) :
            null;
    }

    /**
     * return a closure as string
     *
     * @return string
     */
    public function closureToMongo()
    {
        return <<<'PHP'
$return = ($value instanceof \Graviton\DocumentBundle\Entity\ExtReference ?
    \MongoDBRef::create($value->getRef(), $value->getId()) :
    null);
PHP;
    }
}
