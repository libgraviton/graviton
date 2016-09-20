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
     * @var Array $selectedFields
     */
    protected $selectedFields;

    /**
     * @var Boolean $isSelect
     */
    protected $isSelect;

    /**
     * Injection of the global request_stack to access the selected Fields via Query-Object
     * @param RequestStack $requestStack the global request_stack
     * @return void
     */
    public function getSelectedFieldsFromRQL(RequestStack $requestStack)
    {
        $currentRequest = $requestStack->getCurrentRequest();
        $this->selectedFields = [];
        $this->isSelect = false;
        if ($currentRequest) {
            $rqlQuery = $currentRequest->get('rqlQuery');
            if ($rqlQuery && $rqlQuery instanceof Query) {
                $select = $rqlQuery->getSelect();
                if ($select) {
                    $this->isSelect = true;
                    $this->selectedFields = $select->getFields();
                    // get the nested fields as well
                    $nestedFields = [];
                    foreach ($this->selectedFields as $key => $field) {
                        if (strstr($field, '.')) {
                            $nestedFields=array_merge($nestedFields, explode('.', $field));
                            unset($this->selectedFields[$key]);
                        }
                    }
                    $this->selectedFields = array_merge($this->selectedFields, $nestedFields);
                    // id is always included in response (bug/feature)?
                    if (! in_array('id', $this->selectedFields)) {
                        $this->selectedFields[] = 'id';
                    };
                }
            }
        }
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
        // nothing selected, default serialization
        if (! $this->isSelect) {
            return false;
        }
        return ! in_array($property->name, $this->selectedFields);
    }
}
