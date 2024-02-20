<?php
/**
 * UrlConstraintBuilder class file
 */

namespace Graviton\SchemaBundle\Constraint\Builder;

use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Document\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ArrayOfObjectConstraintBuilder implements ConstraintBuilderInterface
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
        if ($fieldDefinition['fieldName'] == 'notEmptyArray') {
            $hans = 2;
        }

        $isArray = false;
        $type = $fieldDefinition['schemaType'];

        if (str_ends_with($fieldDefinition['schemaType'], '[]')) {
            $type = substr($type, 0, -2);
            $isArray = true;
        }

        // already done?
        if (isset($schemaField['type']) && $schemaField['type'] == 'array') {
            // already done!
            return $schemaField;
        }

        if (str_starts_with($type, 'class:')) {
            $className = explode('\\', $type);
            $shortClassName = array_pop($className);
            $type = '#/components/schemas/'.$shortClassName;
        }

        if ($isArray) {
            $schemaField['type'] = 'array';
            if (str_starts_with($type, '#')) { # ref!
                $schemaField['items'] = ['$ref' => $type];
            } else {
                $schemaField['items'] = ['type' => $type];
            }
        } else {
            if (str_starts_with($type, '#')) { # ref!
                //
                //$schemaField['additionalProperties'] = ['$ref' => $type];
                $schemaField['type'] = 'object';
                $schemaField['schema'] = ['$ref' => $type];
            } else {
                // ELSE case we DO NOT DO ANYTHING -> leave it to others!
            }
        }

        return $schemaField;
    }

    #[\Override] public function supportsConstraint($type, array $options = [])
    {
        return false;
    }

    #[\Override] public function buildConstraint($fieldName, Schema $property, DocumentModel $model, array $options)
    {
        // TODO: Implement buildConstraint() method.
    }
}
