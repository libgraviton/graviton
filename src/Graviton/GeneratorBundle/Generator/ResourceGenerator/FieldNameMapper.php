<?php
/**
 * add singular names to fields
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldNameMapper implements FieldMapperInterface
{

    /**
     * @var Inflector $inflector
     */
    private Inflector $inflector;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * @param array $field   mappable field with type attribute
     * @param mixed $context context for mapper to check
     *
     * @return array
     */
    public function map($field, $context = null)
    {
        $field['singularName'] = $this->inflector->singularize($field['fieldName']);
        return $field;
    }
}
