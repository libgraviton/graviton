<?php
/**
 * a dummy constraint builder
 */

namespace Graviton\SchemaBundle\Tests\ConstraintBuilder\Builder;

use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Constraint\Builder\ConstraintBuilderInterface;
use Graviton\SchemaBundle\Document\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DummyBuilderA implements ConstraintBuilderInterface
{

    /**
     * @var array
     */
    public $options;

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
        return ($type === 'DummyA');
    }

    /**
     * Adds constraints to the property
     *
     * @param string        $fieldName field name
     * @param Schema        $property  property
     * @param DocumentModel $model     parent model
     * @param array         $options   the constraint options
     *
     * @throws \Exception
     *
     * @return Schema the modified property
     */
    public function buildConstraint($fieldName, Schema $property, DocumentModel $model, array $options)
    {
        $this->options = $options;

        if (!isset($options[0]) || !$options[0] instanceof \stdClass) {
            throw new \Exception('Not good params');
        }

        $property->setTitle('THIS WAS SET BY DUMMY-A');
        return $property;
    }
}
