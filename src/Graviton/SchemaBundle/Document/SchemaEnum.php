<?php
/**
 * Graviton SchemaEnum Document
 */

namespace Graviton\SchemaBundle\Document;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaEnum
{
    /**
     * @var array
     */
    protected $values;

    /**
     * Constructor
     *
     * @param array $values enum values
     */
    public function __construct(array $values)
    {
        $this->setValues($values);
    }

    /**
     * gets properties
     *
     * @return Schema|boolean properties
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * sets properties
     *
     * @param array $values enum values
     *
     * @return void
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }
}
