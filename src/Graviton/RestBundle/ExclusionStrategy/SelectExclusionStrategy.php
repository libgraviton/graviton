<?php
/**
 * Class for exclusion strategies.
 */
namespace Graviton\RestBundle\ExclusionStrategy;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Context;
use phpDocumentor\Reflection\Types\Integer;
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
     * @var Array $selectedFields contains all the selected fields and its combinations with nested fields
     */
    protected $selectedFields;

    /**
     * @var Array $selectedLeafs contains the leafs of the selection "tree"
     */
    protected $selectedLeafs;

    /**
     * @var Boolean $isSelect
     */
    protected $isSelect;

    /**
     * @var array $currentPath
     */
    protected $currentPath;

    /**
     * @var Integer $currentDepth
     */
    protected $currentDepth;

    /**
     * SelectExclusionStrategy constructor.
     * Comstructor Injection of the global request_stack to access the selected Fields via Query-Object
     * @param RequestStack $requestStack the global request_stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->currentDepth = 0;
        $this->currentPath = [];
        $this->getSelectedFieldsFromRQL();
    }

    /**
     * Initializing $this->selectedFields and $this->isSelect
     * getting the fields that should be really serialized and setting the switch that there is actually a select
     * called once in the object, so shouldSkipProperty can use the information for every field
     * @return void
     */
    private function getSelectedFieldsFromRQL()
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $this->selectedFields = [];
        $this->selectedLeafs = [];
        $this->isSelect = false;
        if ($currentRequest
            && ($rqlQuery = $currentRequest->get('rqlQuery')) instanceof Query
            && $select = $rqlQuery->getSelect()
        ) {
            $this->isSelect = true;
            // the selected fields are the leafs
            $this->selectedLeafs = $select->getFields();
            // get all combinations of leaf with nested fields
            foreach ($this->selectedLeafs as $selectedLeaf) {
                //clean up $
                $selectedLeaf = str_replace('$', '', $selectedLeaf);
                $this->selectedFields[] = $selectedLeaf;
                if (strstr($selectedLeaf, '.')) {
                    $nestedFields = explode('.', $selectedLeaf);
                    for ($i = 1; $i < count($nestedFields); $i++) {
                        $this->selectedFields[] = implode('.', array_slice($nestedFields, 0, $i));
                    }
                }
            }
            // id is always included in response (bug/feature)?
            if (!in_array('id', $this->selectedFields)) {
                $this->selectedFields[] = 'id';
            };
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
     * Skipping properties who are not selected if there is a select in rql.
     * @param PropertyMetadata $property the property to be serialized
     * @param Context          $context  the context for serialization
     * @return boolean
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        // nothing selected, default serialization
        if (! $this->isSelect) {
            return false;
        }

        // calculate the currentPath in the "tree" of the document to be serialized
        $depth = $context->getDepth() - 1;
        if ($depth <= $this->currentDepth) {
            // reduce the currentPath by one step
            array_pop($this->currentPath);
            // start a new currentPath
            if ($depth == 0) {
                $this->currentPath = [];
            }
        }
        $this->currentPath[] = $property->name;
        $this->currentDepth = $depth;
        $currentPath = implode('.', $this->currentPath);

        // test the currentpath
        $skip = ! in_array($currentPath, $this->selectedFields);
        // give it a second chance if its a nested path, go through all the selectedLeafs
        if ($this->currentDepth>0 && $skip) {
            foreach ($this->selectedLeafs as $leaf) {
                if (strstr($currentPath, $leaf)) {
                    $skip = false;
                    break;
                }
            }
        }
        return $skip;
    }
}
