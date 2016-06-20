<?php
/**
 * Graviton SchemaType Document
 */

namespace Graviton\SchemaBundle\Document;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaType
{
    /**
     * @var array
     */
    protected $types;

    /**
     * Constructor
     *
     * @param array $types types
     */
    public function __construct(array $types)
    {
        $this->setTypes($types);
    }

    /**
     * gets properties
     *
     * @return Schema|boolean properties
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * sets types
     *
     * @param array $types types
     *
     * @return void
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * make this string castable. will obviously not work for multiple types,
     * but it will only be the *real* type and null anyway.. so we try to accomodate for that.
     *
     * @return string the type
     */
    public function __toString()
    {
        $types = $this->types;

        if (count($this->types) === 1) {
            return array_pop($types);
        } else {
            $nullIndex = array_search('null', $types);
            if ($nullIndex !== false) {
                unset($types[$nullIndex]);
                return array_pop($types);
            }
        }

        return '';
    }
}
