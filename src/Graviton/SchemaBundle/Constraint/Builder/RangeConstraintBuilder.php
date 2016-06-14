<?php
/**
 * RangeConstraintBuilder class file
 */

namespace Graviton\SchemaBundle\Constraint\Builder;

use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Document\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RangeConstraintBuilder implements ConstraintBuilderInterface
{

    /**
     * @var array
     */
    private $types = [
        'Range',
        'GreaterThanOrEqual',
        'LessThanOrEqual'
    ];

    /**
     * @var string
     */
    private $type;
    
    /**
     * if this builder supports a given constraint
     *
     * @param string $type    Field type
     * @param array  $options Options
     *
     * @return bool
     */
    public function supportsConstraint($type, array $options = [])
    {
        if (in_array($type, $this->types)) {
            $this->type = $type;
            return true;
        }

        return false;
    }

    /**
     * Adds constraints to the property
     *
     * @param string        $fieldName field name
     * @param Schema        $property  property
     * @param DocumentModel $model     parent model
     * @param array         $options   the constraint options
     *
     * @return Schema the modified property
     */
    public function buildConstraint($fieldName, Schema $property, DocumentModel $model, array $options)
    {
        foreach ($options as $option) {
            if ($option->name == 'min' && $this->type == 'Range') {
                $property->setNumericMinimum(floatval($option->value));
            }
            if ($option->name == 'max' && $this->type == 'Range') {
                $property->setNumericMaximum(floatval($option->value));
            }
            if ($option->name == 'value' && $this->type == 'GreaterThanOrEqual') {
                $property->setNumericMinimum(floatval($option->value));
            }
            if ($option->name == 'value' && $this->type == 'LessThanOrEqual') {
                $property->setNumericMaximum(floatval($option->value));
            }
        }

        return $property;
    }
}
