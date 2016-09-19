<?php
/**
 * Class for exclusion strategies.
 */
namespace Graviton\RestBundle\ExclusionStrategy;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Context;
use Symfony\Component\HttpFoundation\RequestStack;
use Xiag\Rql\Parser\Query;

/**
 * In this Strategy we skip all properties on first level who are not selected if there is a select in rql.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SelectExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @var RequestStack $requestStack
     */
    protected $requestStack;

    /**
     * for injection of the global request_stack to access the XiagQuery-Object
     * @param RequestStack $requestStack the global request_stack
     * @return void
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @InheritDoc: Whether the class should be skipped.
     * @param ClassMetadata $metadata         the ClassMetadata for the Class of the property to be serialized
     * @param Context       $navigatorContext the context for serialization
     * @return boolean
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $navigatorContext)
    {
        return false;
    }

    /**
     * @InheritDoc: Whether the property should be skipped.
     * Skipping properties on first level who are not selected if there is a select in rql.
     * @param PropertyMetadata $property the property to be serialized
     * @param Context          $context  the context for serialization
     * @return boolean
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        // we are only dealing with the first level of the JSON here
        if ($context->getDepth() > 1) {
            return false;
        }

        $return = false;
        $xiagQuery = $this->requestStack->getCurrentRequest()->get('rqlQuery');
        if ($xiagQuery && $xiagQuery instanceof Query) {
            $select = $xiagQuery->getSelect();
            if ($select) {
                $return = ! in_array($property->name, $select->getFields());
            }
        }
        return $return;
    }
}
