<?php
/**
 * FieldNameSearchListener class file
 */

namespace Graviton\DocumentBundle\Listener;

use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\ElemMatchNode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Graviton\RqlParser\Node\Query\AbstractComparisonOperatorNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldNameSearchListener
{
    /**
     * @var array
     */
    private $fields;
    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param array        $fieldsMapping Fields mapping
     * @param RequestStack $requestStack  Request stack
     */
    public function __construct(array $fieldsMapping, RequestStack $requestStack)
    {
        $this->fields = $fieldsMapping;
        $this->request = $requestStack->getCurrentRequest();
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

        $fieldName = $this->getDocumentFieldName($node->getField(), $event->getContext());
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
     * @param string    $searchName  Exposed field name from RQL query
     * @param \SplStack $nodeContext Current node context
     * @return string|bool Field name or FALSE
     */
    private function getDocumentFieldName($searchName, \SplStack $nodeContext)
    {
        $route = $this->request->attributes->get('_route');
        if (!isset($this->fields[$route])) {
            throw new \LogicException(sprintf('No field mapping found for route "%s"', $route));
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

        $documentField = array_search($fieldName, $this->fields[$route], true);
        if ($documentField === false) {
            return false;
        }
        if ($fieldPrefix === '') {
            return $documentField;
        }

        $documentPrefix = array_search(rtrim($fieldPrefix, '.'), $this->fields[$route], true);
        if ($documentPrefix === false) {
            return false;
        }

        $documentPrefix = $documentPrefix.'.';
        if (strpos($documentField, $documentPrefix) !== 0) {
            return false;
        }

        return substr($documentField, strlen($documentPrefix));
    }
}
