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
class ExtrefConstraintBuilder implements ConstraintBuilderInterface
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
        if ($fieldDefinition['type'] == 'extref') {
            $schemaField['type'] = 'string';
            $schemaField['format'] = 'extref';
            if (isset($fieldDefinition['collection'])) {
                $schemaField['x-collection'] = $fieldDefinition['collection'];
            }
        }

        return $schemaField;
    }

    #[\Override] public function supportsConstraint($type, array $options = [])
    {
        // TODO: Implement supportsConstraint() method.
    }

    #[\Override] public function buildConstraint($fieldName, Schema $property, DocumentModel $model, array $options)
    {
        // TODO: Implement buildConstraint() method.
    }
}
