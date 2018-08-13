<?php
/**
 * RqlSearchNodeListener
 */

namespace Graviton\DocumentBundle\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Event\VisitPostEvent;
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
    private $node;

    /**
     * @var Builder
     */
    private $builder;
    
    /**
     * @var SolrQuery
     */
    private $solrQuery;

    /**
     * search mode for current request
     *
     * @var string
     */
    private $currentSearchMode;

    /**
     * constant for search mode mongo
     */
    const SEARCHMODE_MONGO = 'mongo';

    /**
     * constant for search mode solr
     */
    const SEARCHMODE_SOLR = 'solr';

    /**
     * constructor
     *
     * @param SolrQuery $solrQuery solr query service
     */
    public function __construct(SolrQuery $solrQuery)
    {
        $this->solrQuery = $solrQuery;
    }

    /**
     * @param VisitNodeEvent $event node event to visit
     *
     * @return VisitNodeEvent
     */
    public function onVisitNode(VisitNodeEvent $event)
    {
        // any search?
        if (!$event->getNode() instanceof SearchNode) {
            return $event;
        }

        $this->node = $event->getNode();
        $this->builder = $event->getBuilder();

        // which mode?
        if ($this->getSearchMode() === self::SEARCHMODE_SOLR) {
            $this->handleSearchSolr();
        } else {
            $this->handleSearchMongo();
        }

        $event->setBuilder($this->builder);
        $event->setNode($this->node);

        return $event;
    }

    
    public function onVisitPost(VisitPostEvent $event)
    {
        // only do things here if we're using solr
        if (self::SEARCHMODE_SOLR !== $this->currentSearchMode) {
            return;
        }

        $builder = $event->getBuilder();
        $query = $event->getQuery();

        var_dump($query); die;


        //var_dump($this->node->getSearchTerms());

        $this->solrQuery->query("müller");


        echo "solllrrr"; die;

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
        // will be done in visitPost, just memorize that we're using solr
        $this->currentSearchMode = self::SEARCHMODE_SOLR;
    }

    private function getSearchMode()
    {
        $this->solrQuery->setClassName($this->getDocumentClassName());
        if ($this->solrQuery->isConfigured()) {
            return self::SEARCHMODE_SOLR;
        }

        return self::SEARCHMODE_MONGO;
    }

    private function getSolrInformation()
    {
        $className = $this->getDocumentClassName();
        if (isset($this->solrMap[$className])) {
            return $this->solrMap[$className];
        }
        return null;
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
