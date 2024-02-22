<?php
/**
 * RangeConstraintBuilder class file
 */

namespace Graviton\SchemaBundle\Constraint\Builder;

use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Document\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RangeConstraintBuilder implements ConstraintBuilderInterface
{

    /**
     * @var array
     */
    private array $types = [
        'Range',
        'GreaterThanOrEqual',
        'LessThanOrEqual'
    ];

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
        if (isset($fieldDefinition['constraints']['Range'])) {
            $options = $fieldDefinition['constraints']['Range'];
            if (isset($options['min'])) {
                $schemaField['minimum'] = floatval($options['min']);
            }
            if (isset($options['max'])) {
                $schemaField['maximum'] = floatval($options['max']);
            }
        }

        if (isset($fieldDefinition['constraints']['GreaterThanOrEqual'])) {
            $options = $fieldDefinition['constraints']['GreaterThanOrEqual'];
            if (isset($options['value'])) {
                $schemaField['minimum'] = floatval($options['value']);
            }
        }

        if (isset($fieldDefinition['constraints']['LessThanOrEqual'])) {
            $options = $fieldDefinition['constraints']['LessThanOrEqual'];
            if (isset($options['value'])) {
                $schemaField['maximum'] = floatval($options['value']);
            }
        }

        return $schemaField;
    }

    /**
     * @var string
     */
    private $type;
    
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
        if (in_array($type, $this->types)) {
            $this->type = $type;
            return true;
        }

        return false;
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
        foreach ($options as $option) {
            if ($option->name == 'min' && $this->type == 'Range') {
                $property->setNumericMinimum(floatval($option->value));
            }
            if ($option->name == 'max' && $this->type == 'Range') {
                $property->setNumericMaximum(floatval($option->value));
            }
            if ($option->name == 'value' && $this->type == 'GreaterThanOrEqual') {
                $property->setNumericMinimum(floatval($option->value));
            }
            if ($option->name == 'value' && $this->type == 'LessThanOrEqual') {
                $property->setNumericMaximum(floatval($option->value));
            }
        }

        return $property;
    }
}
