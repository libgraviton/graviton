<?php
/**
 * RqlSearchNodeListener
 *
 * on rql search() operations, this listener does the correct stuff at the correct time.
 */

namespace Graviton\DocumentBundle\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Event\VisitPostEvent;
use Graviton\Rql\Node\SearchNode;
use Graviton\RqlParser\Node\SelectNode;

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
     * @var boolean
     */
    private $expr = false;

    /**
     * @var Expr
     */
    private $exprNode;

    /**
     * @var string
     */
    private $className;

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
     * gets called during the visit of a normal search node
     *
     * @param VisitNodeEvent $event node event to visit
     *
     * @return VisitNodeEvent event object
     */
    public function onVisitNode(VisitNodeEvent $event)
    {
        // any search?
        if (!$event->getNode() instanceof SearchNode || $event->getNode()->isVisited()) {
            return $event;
        }

        $this->node = $event->getNode();
        $this->builder = $event->getBuilder();
        $this->expr = $event->isExpr();
        $this->className = $event->getClassName();

        // which mode?
        if ($this->getSearchMode() === self::SEARCHMODE_SOLR) {
            $this->handleSearchSolr();
        } else {
            $this->handleSearchMongo();
        }

        $event->setBuilder($this->builder);
        $event->setNode($this->node);
        $event->setExprNode($this->exprNode);

        return $event;
    }

    /**
     * gets called after all the single search nodes have been worked on - like a 'post rql' event.
     * here we only do things for solr as we want to set the list of record ids after all is done (sd
     * selects, sort and limit)
     *
     * @param VisitPostEvent $event the event
     *
     * @return VisitPostEvent event
     */
    public function onVisitPost(VisitPostEvent $event)
    {
        // only do things here if we're using solr
        if (self::SEARCHMODE_SOLR !== $this->currentSearchMode) {
            return $event;
        }

        $idList = $this->solrQuery->query(
            $event->getQuery()->getQuery(),
            $event->getQuery()->getLimit()
        );

        /**
         * we need an aggregation here as mongo
         * needs to sort the resulting array based on the $idList array we
         * received from solr..
         */

        $aggregation = $event->getRepository()->createAggregationBuilder();

        $aggregation
            ->match()
            ->field('_id')
            ->in($idList);

        // do we have a select?
        $select = $event->getQuery()->getSelect();
        if ($select instanceof SelectNode) {
            $aggregation
                ->project()
                ->includeFields($select->getFields());
        }

        $aggregation
            ->addFields()
            ->field('_theSorter')
            ->indexOfArray($idList, '$_id');

        $aggregation->sort('_theSorter', 1);

        $event->setAggregationOverride($aggregation);

        return $event;
    }

    /**
     * in case of a search() in mongo (using the text index), this logic here should be executed.
     *
     * @return void
     */
    private function handleSearchMongo()
    {
        $this->node->setVisited(true);

        $searchArr = [];
        foreach ($this->node->getSearchTerms() as $string) {
            $searchArr[] = "\"{$string}\"";
        }
        $this->builder->sortMeta('score', 'textScore');

        $basicTextSearchValue = implode(' ', $searchArr);

        if ($this->expr) {
            $this->exprNode = $this->builder->expr()->text($basicTextSearchValue);
        } else {
            $this->builder->addAnd($this->builder->expr()->text($basicTextSearchValue));
        }
    }

    /**
     * in case of configured and active solr search, this is executed.
     * we don't do anything here, we just remember that we use solr and then do our stuff in the post event
     * function.
     *
     * @return void
     */
    private function handleSearchSolr()
    {
        // will be done in visitPost, just memorize that we're using solr
        $this->currentSearchMode = self::SEARCHMODE_SOLR;
    }

    /**
     * returns which search backend to use
     *
     * @return string search mode constant
     */
    private function getSearchMode()
    {
        $this->solrQuery->setClassName($this->className);
        if ($this->solrQuery->isConfigured()) {
            return self::SEARCHMODE_SOLR;
        }

        return self::SEARCHMODE_MONGO;
    }
}
