<?php
/**
 * map fields using multiple mappers
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FieldMapper implements FieldMapperInterface
{
    /**
     * @var FieldMapperInterface[]
     */
    private $mappers = [];

    /**
     * @param FieldMapperInterface $mapper mapper to add
     *
     * @return void
     */
    public function addMapper(FieldMapperInterface $mapper)
    {
        $this->mappers[] = $mapper;
    }

    /**
     * builds the initial fields array with a json definition
     *
     * @param JsonDefinition $jsonDefinition definition
     *
     * @return array fields
     */
    public function buildFields(JsonDefinition $jsonDefinition)
    {
        $fields = [];
        foreach ($jsonDefinition->getFields() as $field) {
            if ($field->getName() != 'id') {
                $fields[] = [
                    'fieldName' => $field->getName(),
                    'type' => $field->getTypeDoctrine()
                ];
            }
        }

        return $fields;
    }

    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context = null)
    {
        foreach ($this->mappers as $mapper) {
            $field = $mapper->map($field, $context);
        }
        return $field;
    }
}
