<?php
/**
 * FieldNameSearchListener class file
 */

namespace Graviton\DocumentBundle\Listener;

use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\ElemMatchNode;
use Graviton\RqlParser\Node\Query\AbstractComparisonOperatorNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class FieldNameSearchListener
{

    /**
     * Constructor
     *
     * @param array $fields Fields mapping
     */
    public function __construct(private array $fields)
    {
    }

    /**
     * @param VisitNodeEvent $event node event to visit
     *
     * @return VisitNodeEvent
     */
    public function onVisitNode(VisitNodeEvent $event)
    {
        $node = $event->getNode();
        if (!$node instanceof AbstractComparisonOperatorNode) {
            return $event;
        }

        $fieldName = $this->getDocumentFieldName($event->getClassName(), $node->getField(), $event->getContext());
        if ($fieldName === false) {
            return $event;
        }

        $copy = clone $node;
        $copy->setField(strtr($fieldName, ['.0.' => '.']));
        $event->setNode($copy);
        return $event;
    }

    /**
     * Get document field name by query name
     *
     * @param string    $className   class name
     * @param string    $searchName  Exposed field name from RQL query
     * @param \SplStack $nodeContext Current node context
     *
     * @return string|bool Field name or FALSE
     */
    private function getDocumentFieldName($className, $searchName, \SplStack $nodeContext)
    {
        if (!isset($this->fields[$className])) {
            throw new \LogicException(sprintf('No field mapping found for class "%s"', $className));
        }

        $fieldName = $searchName;
        $fieldPrefix = '';
        foreach ($nodeContext as $parentNode) {
            if ($parentNode instanceof ElemMatchNode) {
                $fieldName = $parentNode->getField().'.0.'.$fieldName;
                $fieldPrefix = $parentNode->getField().'.0.'.$fieldPrefix;
            }
        }
        $fieldName = strtr($fieldName, ['..' => '.0.']);
        $fieldPrefix = strtr($fieldPrefix, ['..' => '.0.']);

        $documentField = array_search($fieldName, $this->fields[$className], true);
        if ($documentField === false) {
            return false;
        }
        if ($fieldPrefix === '') {
            return $documentField;
        }

        $documentPrefix = array_search(rtrim($fieldPrefix, '.'), $this->fields[$className], true);
        if ($documentPrefix === false) {
            return false;
        }

        $documentPrefix = $documentPrefix.'.';
        if (!str_starts_with($documentField, $documentPrefix)) {
            return false;
        }

        return substr($documentField, strlen($documentPrefix));
    }
}
