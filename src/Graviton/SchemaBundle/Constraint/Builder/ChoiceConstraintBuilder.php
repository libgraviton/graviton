<?php
/**
 * ChoiceConstraintBuilder class file
 *
 * a constraint builder that renders an enum for the schema
 */

namespace Graviton\SchemaBundle\Constraint\Builder;

use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Document\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ChoiceConstraintBuilder implements ConstraintBuilderInterface
{
    
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
        return ($type === 'Choice');
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
        $enumValue = array_reduce(
            $options,
            function ($carry, $option) {
                if ($option->name == 'choices') {
                    return explode('|', $option->value);
                }
            }
        );

        // is this a numeric field? convert values
        if ($property->getType() == 'integer') {
            $enumValue = array_map('intval', $enumValue);
        }

        if (is_array($enumValue)) {
            $property->setEnum($enumValue);
        }

        return $property;
    }
}
