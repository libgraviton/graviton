<?php
/**
 * add singular names to fields
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\DefinitionElementInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FieldJsonMapper
{
    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context)
    {
        if ($context instanceof JsonDefinition &&
            $context->getField($field['fieldName']) instanceof DefinitionElementInterface
        ) {
            $fieldInformation = $context->getField($field['fieldName'])
                ->getDefAsArray();

            // in this context, the default type is the doctrine type..
            if (isset($fieldInformation['doctrineType'])) {
                $fieldInformation['type'] = $fieldInformation['doctrineType'];
            }

            $field = array_merge($field, $fieldInformation);
        }

        return $field;
    }
}
