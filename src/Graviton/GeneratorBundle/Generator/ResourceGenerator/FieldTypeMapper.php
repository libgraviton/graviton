<?php
/**
 * map field types for ResourceGenerator
 *
 * Use to generate corresponding serializerTypes from json-def fields.
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        $field['serializerType'] = $field['type'];
        if (substr($field['type'], -2) == '[]') {
            $field['serializerType'] = sprintf('array<%s>', substr($field['type'], 0, -2));
        }

        // @todo this assumtion is a hack and needs fixing
        if ($field['type'] === 'array') {
            $field['serializerType'] = 'array<string>';
        }

        if ($field['type'] === 'object') {
            $field['serializerType'] = 'array';
        }

        return $field;
    }
}
