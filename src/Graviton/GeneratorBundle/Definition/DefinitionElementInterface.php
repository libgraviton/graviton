<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * An interface having some common stuff for all definitions for easier
 * use of those objects..
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface DefinitionElementInterface
{

    const TYPE_STRING = 'varchar';

    const TYPE_INTEGER = 'int';

    const TYPE_LONG = 'bigint';

    const TYPE_DOUBLE = 'double';

    const TYPE_DATETIME = 'datetime';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_HASH = 'hash';

    /**
     * Returns whether this element is a field
     *
     * @return boolean
     */
    public function isField();

    /**
     * Returns whether this element is a hash
     *
     * @return boolean
     */
    public function isHash();

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
     * @return string[] definition
     */
    public function getDefAsArray();
}
