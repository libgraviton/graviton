<?php
/**
 * JsonDefinitionRel class file
 */

namespace Graviton\GeneratorBundle\Definition;

/**
 * JSON definition relation field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionRel extends JsonDefinitionField
{
    /**
     * @var Schema\Relation
     */
    private $relation;

    /**
     * Constructor
     *
     * @param string          $name       Field name
     * @param Schema\Field    $definition Definition
     * @param Schema\Relation $relation   Relation
     */
    public function __construct($name, Schema\Field $definition, Schema\Relation $relation = null)
    {
        $this->relation = $relation;
        parent::__construct($name, $definition);
    }

    /**
     * Returns the whole definition in array form
     *
     * @return array Definition
     */
    public function getDefAsArray()
    {
        return array_replace(
            parent::getDefAsArray(),
            [
                'type'              => $this->getType(),
                'doctrineType'      => $this->getTypeDoctrine(),
                'serializerType'    => $this->getTypeSerializer(),
                'relType'           => $this->relation === null ? self::REL_TYPE_REF : $this->relation->getType(),
                'isClassType'       => true,
            ]
        );
    }

    /**
     * Returns the field type in a doctrine-understandable way..
     *
     * @return string Type
     */
    public function getTypeDoctrine()
    {
        return $this->getClassName();
    }

    /**
     * Returns the field type
     *
     * @return string Type
     */
    public function getType()
    {
        return $this->getClassName();
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        return $this->getClassName();
    }

    /**
     * Returns the defined class name
     *
     * @return string class name
     */
    private function getClassName()
    {
        return strtr($this->getDef()->getType(), ['class:' => '', '[]' => '']);
    }
}
