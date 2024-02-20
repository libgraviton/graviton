<?php
/**
 * CountConstraintBuilder class file
 */

namespace Graviton\SchemaBundle\Constraint\Builder;

use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Document\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CountConstraintBuilder implements ConstraintBuilderInterface
{

    /**
     * gives the constraintbuilder the opportunity to alter the json schema for that field.
     *
     * @param array $schemaField     the basic field that will be in the schema
     * @param array $fieldDefinition definition as seen by the generator
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition) : array
    {
        if (isset($fieldDefinition['constraints']['Count'])) {
            $options = $fieldDefinition['constraints']['Count'];
            if (isset($schemaField['nullable'])) {
                $schemaField['nullable'] = false;
            }

            if (isset($options['min'])) {
                $schemaField['minItems'] = intval($options['min']);
            }
            if (isset($options['max'])) {
                $schemaField['maxItems'] = intval($options['max']);
            }
        }

        return $schemaField;
    }

    /**
     * if this builder supports a given constraint
     *
     * @param string $type    Field type
     * @param array  $options Options
     *
     * @return bool
     */
    public function supportsConstraint($type, array $options = [])
    {

    }

    /**
     * Adds constraints to the property
     *
     * @param string        $fieldName field name
     * @param Schema        $property  property
     * @param DocumentModel $model     parent model
     * @param array         $options   the constraint options
     *
     * @return Schema the modified property
     */
    public function buildConstraint($fieldName, Schema $property, DocumentModel $model, array $options)
    {
        if (in_array('array', $property->getType()->getTypes())) {
            foreach ($options as $option) {
                if ($option->name == 'min') {
                    $property->setMinItems(intval($option->value));
                }
                if ($option->name == 'max') {
                    $property->setMaxItems(intval($option->value));
                }
            }
        }

        return $property;
    }
}
