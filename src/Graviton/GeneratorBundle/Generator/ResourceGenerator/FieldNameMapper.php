<?php
/**
 * add singular names to fields
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Doctrine\Common\Inflector\Inflector;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FieldNameMapper implements FieldMapperInterface
{
    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context = null)
    {
        $field['singularName'] = Inflector::singularize($field['fieldName']);
        return $field;
    }
}
