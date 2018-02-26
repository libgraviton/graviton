<?php
/**
 * Verify require Title for fields. Use fieldName otherwise
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldTitleMapper implements FieldMapperInterface
{
    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context = null)
    {
        $title = 'Please add title';

        if (array_key_exists('title', $field) && !empty($field['title'])) {
            $title = $field['title'];
        } elseif (array_key_exists('fieldName', $field) && !empty($field['fieldName'])) {
            $value = $field['fieldName'];
            // Field have dots
            if (strpos($value, '.') !== false) {
                $value = str_replace('.', ' ', str_replace('.0', '.array', $value));
            }
            $value = preg_replace('/(?<=\\w)(?=[A-Z])/', " $1", $value);
            $title = ucfirst(strtolower($value));
        }
        $field['title'] = trim($title);
        return $field;
    }
}
