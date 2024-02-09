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
class ClassTypeConstraintBuilder implements ConstraintBuilderInterface
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
        if (str_starts_with($fieldDefinition['schemaType'], 'class:')) {
            $className = explode('\\', $fieldDefinition['schemaType']);
            $shortClassName = array_pop($className);
            $schemaField['type'] = 'object';
            $schemaField['additionalProperties'] = [
                '$ref' => '#/components/schemas/'.$shortClassName
            ];
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
