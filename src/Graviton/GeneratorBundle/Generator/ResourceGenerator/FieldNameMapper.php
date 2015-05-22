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
class FieldNameMapper
{
    /**
     * @param array $field mappable field with type attribute
     *
     * @return array
     */
    public function map($field)
    {
        $field['singularName'] = Inflector::singularize($field['fieldName']);
        return $field;
    }
}
