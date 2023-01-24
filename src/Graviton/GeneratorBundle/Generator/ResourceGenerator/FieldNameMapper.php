<?php
/**
 * add singular names to fields
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Symfony\Component\String\Inflector\EnglishInflector;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldNameMapper implements FieldMapperInterface
{

    /**
     * @var EnglishInflector $inflector
     */
    private EnglishInflector $inflector;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inflector = new EnglishInflector();
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
