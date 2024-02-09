<?php
/**
 * map field types for ResourceGenerator
 *
 * Use to generate corresponding serializerTypes from json-def fields.
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Graviton\GeneratorBundle\Definition\DefinitionElementInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldTypeMapper implements FieldMapperInterface
{
    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context = null)
    {
        /*
        $field['serializerType'] = $field['type'];
        if (substr($field['type'], -2) == '[]') {
            $field['serializerType'] = sprintf('array<%s>', substr($field['type'], 0, -2));
        }

        if ($field['type'] === 'array') {
            $field['serializerType'] = 'array<string>';
        }

        if ($field['type'] === 'object') {
            $field['serializerType'] = 'array';
        }*/

        if ($context instanceof JsonDefinition &&
            $context->getField($field['fieldName']) instanceof DefinitionElementInterface
        ) {
            $fieldInformation = $context->getField($field['fieldName'])
                ->getDefAsArray();

            if (empty($fieldInformation['schemaType'])) {
                $fieldInformation['schemaType'] = $fieldInformation['type'];
            }

            // in this context, the default type is the doctrine type..
            if (isset($fieldInformation['doctrineType'])) {
                $fieldInformation['type'] = $fieldInformation['doctrineType'];
            }

            $field = array_merge($field, $fieldInformation);
        }

        return $field;
    }
}
