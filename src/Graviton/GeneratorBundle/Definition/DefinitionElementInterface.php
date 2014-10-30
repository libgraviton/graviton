<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * An interface having some common stuff for all definitions for easier
 * use of those objects..
 *
 * Important: Here we collect all the types in those constants. All "primitive" types
 * (including DateTime) SHOULD be referenced by their MariaDB-types as a common descriptor.
 * That way, we have an uniform lingo of those types.
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface DefinitionElementInterface
{


    const TYPE_STRING = 'VARCHAR';

    const TYPE_INTEGER = 'INT';

    const TYPE_DATETIME = 'DATETIME';

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

}
