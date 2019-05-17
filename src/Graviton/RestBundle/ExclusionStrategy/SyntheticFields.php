<?php
/**
 * serializer exclusion strategy for synthetic fields -> those should never be rendered!
 */

namespace Graviton\RestBundle\ExclusionStrategy;

use Graviton\CoreBundle\Util\CoreUtils;
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SyntheticFields implements ExclusionStrategyInterface
{

    private $syntheticFields = [];

    public function __construct($syntheticFields)
    {
        $this->syntheticFields = CoreUtils::parseStringFieldList($syntheticFields);
    }

    /**
     * Whether the class should be skipped.
     *
     * @param ClassMetadata $metadata
     *
     * @return boolean
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        return false;
    }

    /**
     * Whether the property should be skipped.
     *
     * @param PropertyMetadata $property
     *
     * @return boolean
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        if (isset($this->syntheticFields[$property->name])) {
            return true;
        }

        return false;
    }
}
