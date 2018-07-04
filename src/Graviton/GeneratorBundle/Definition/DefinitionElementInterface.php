<?php
/**
 * definitions interface
 */

namespace Graviton\GeneratorBundle\Definition;

/**
 * An interface having some common stuff for all definitions for easier
 * use of those objects..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface DefinitionElementInterface
{
    const TYPE_STRING = 'string';

    const TYPE_VARCHAR = 'varchar';

    const TYPE_TEXT = 'text';

    const TYPE_INTEGER = 'int';

    const TYPE_LONG = 'bigint';

    const TYPE_FLOAT = 'float';

    const TYPE_DOUBLE = 'double';

    const TYPE_DECIMAL = 'decimal';

    const TYPE_DATETIME = 'datetime';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_HASH = 'hash';

    const TYPE_OBJECT = 'object';

    const TYPE_EXTREF = 'extref';

    const REL_TYPE_REF = 'ref';

    const REL_TYPE_EMBED = 'embed';

    /**
     * Returns the name of this field
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the type of this element
     *
     * @return string
     */
    public function getType();

    /**
     * Get type for Doctrine, may be different..
     *
     * @return string
     */
    public function getTypeDoctrine();

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer();

    /**
     * Returns the definition as array
     *
     * @return array definition
     */
    public function getDefAsArray();
}
