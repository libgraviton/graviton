<?php
/**
 * set the hidden and restriction fields
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldHiddenRestrictionMapper implements FieldMapperInterface
{
    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context = null)
    {
        if (array_key_exists('hidden', $field)) {
            $field['hidden'] = (bool) $field['hidden'];
        } else {
            $field['hidden'] = false;
        }

        if (!array_key_exists('restrictions', $field)) {
            $field['restrictions'] = [];
        }

        return $field;
    }
}
