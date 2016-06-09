<?php
/**
 * Graviton SchemaAdditionalProperties Document
 */

namespace Graviton\SchemaBundle\Document;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaAdditionalProperties
{
    /**
     * @var Schema|boolean
     */
    protected $properties;

    /**
     * Constructor
     *
     * @param Schema|boolean $properties either a boolean or a Schema object
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    /**
     * gets properties
     *
     * @return Schema|boolean properties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * sets properties
     *
     * @param Schema|boolean $properties properties
     *
     * @return void
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }
}
