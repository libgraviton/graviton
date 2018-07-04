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
use Xiag\Rql\Parser\Node\SelectNode;

/**
 * In this Strategy we skip all properties on first level who are not selected if there is a select in rql.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SelectExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @var RequestStack $requestStack
     */
    protected $requestStack;

    /**
     * @var Boolean $isSelect
     */
    protected $isSelect;

    /**
     * @var array $currentPath
     */
    protected $currentPath;

    /**
     * @var array for selected tree level
     */
    protected $selectTree = [];

    /**
     * SelectExclusionStrategy constructor.
     * Comstructor Injection of the global request_stack to access the selected Fields via Query-Object
     * @param RequestStack $requestStack the global request_stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->createSelectionTreeFromRQL();
    }

    /**
     * Convert dot string to array.
     *
     * @param string $path string dotted array
     *
     * @return array
     */
    private function createArrayByPath($path)
    {
        $keys = explode('.', $path);
        $val = true;
        $localArray = [];
        for ($i=count($keys)-1; $i>=0; $i--) {
            $localArray = [$keys[$i]=>$val];
            $val = $localArray;
        }
        return $localArray;
    }

    /**
     * Initializing $this->selectedFields and $this->isSelect
     * getting the fields that should be really serialized and setting the switch that there is actually a select
     * called once in the object, so shouldSkipProperty can use the information for every field
     * @return void
     */
    private function createSelectionTreeFromRQL()
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $this->selectTree = [];
        $this->currentPath = [];
        $this->isSelect = false;

        /** @var SelectNode $select */
        if ($currentRequest
            && ($rqlQuery = $currentRequest->get('rqlQuery')) instanceof Query
            && $select = $rqlQuery->getSelect()
        ) {
            $this->isSelect = true;
            // Build simple selected field tree
            foreach ($select->getFields() as $field) {
                $field = str_replace('$', '', $field);
                $arr = $this->createArrayByPath($field);
                $this->selectTree = array_merge_recursive($this->selectTree, $arr);
            }
            $this->selectTree['id'] = true;
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

        // Level starts at 1, so -1 to have it level 0
        $depth = $context->getDepth()-1;

        // Here we build a level based array so we get them all
        $this->currentPath[$depth] = $property->name;
        $keyPath = [];
        foreach ($this->currentPath as $key => $path) {
            if ($key > $depth && array_key_exists($key, $this->currentPath)) {
                unset($this->currentPath[$key]);
            } else {
                $keyPath[] = $path;
            }
        }

        // check path and parent/son should be seen.
        $tree = $this->selectTree;
        foreach ($keyPath as $path) {
            if (!is_array($tree)) {
                break;
            }
            if (array_key_exists($path, $tree)) {
                $tree = $tree[$path];
            } else {
                return true;
            }
        }
        return false;
    }
}
