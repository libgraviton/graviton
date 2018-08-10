<?php
/**
 * RqlSearchNodeListener
 */

namespace Graviton\DocumentBundle\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\SearchNode;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlSearchNodeListener
{
    /**
     * @var SearchNode
     */
    protected $node;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $solrMap;

    /**
     * construct
     *
     * @param ExtReferenceConverterInterface $converter Extref converter
     * @param array                          $fields    map of fields to process
     * @param RequestStack                   $requests  request
     */
    public function __construct(array $solrMap)
    {
        $this->solrMap = $solrMap;
    }

    /**
     * @param VisitNodeEvent $event node event to visit
     *
     * @return VisitNodeEvent
     */
    public function onVisitNode(VisitNodeEvent $event)
    {
        //return $event;

        if (!$event->getNode() instanceof SearchNode) {
            return $event;
        }

        $this->node = $event->getNode();
        $this->builder = $event->getBuilder();

        $this->handleSearchMongo();

        $event->setBuilder($this->builder);
        $event->setNode($this->node);

        /*
        var_dump($this->solrMap);
        var_dump($this->getDocumentClassName());
        var_dump($this->node);
        echo "fred"; die;

        */

        return $event;
    }

    private function handleSearchMongo()
    {
        $this->node->setVisited(true);
        $searchArr = [];
        foreach ($this->node->getSearchTerms() as $string) {
            $searchArr[] = "\"{$string}\"";
        }

        $this->builder->sortMeta('score', 'textScore');

        $basicTextSearchValue = implode(' ', $searchArr);
        $this->builder->addAnd($this->builder->expr()->text($basicTextSearchValue));
    }

    private function handleSearchSolr()
    {

    }

    /**
     * Returns the document class from the query
     *
     * @return string class name
     */
    private function getDocumentClassName()
    {
        // find our class name
        $documentName = $this->builder->getQuery()->getClass()->getName();

        if (!class_exists($documentName)) {
            throw new \LogicException('Could not determine class name from RQL query.');
        }

        return $documentName;
    }

}
