<?php
/**
 * Translator for RQL modification before execution
 */

namespace Graviton\RestBundle\Service;

use Graviton\Rql\Node\SearchNode;
use MongoDate;
use Xiag\Rql\Parser\AbstractNode;
use Xiag\Rql\Parser\DataType\Glob;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\GeNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\LeNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\LikeNode;
use Xiag\Rql\Parser\Query;

/**
 * Class RqlTranslator
 *
 * @package Graviton\RestBundle\Service
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class RqlTranslator
{

    /**
     * Translate a search node into LikeNodes connected with an OrNode
     *
     * @param  SearchNode $searchNode   The given search node to transform
     * @param  array      $searchFields Which fields should be searched for all terms in SearchNode
     * @return SearchNode|OrNode
     */
    public function translateSearchNode(SearchNode $searchNode, $searchFields = array())
    {
        $orNode = new OrNode();

        foreach ($searchFields as $searchField) {

            foreach ($searchNode->getSearchTerms() as $searchTerm) {
                $searchGlob = new Glob('*' . $searchTerm . '*');
                $likeNode = new LikeNode($searchField, $searchGlob);
                $orNode->addQuery($likeNode);

                if (is_numeric($searchTerm)) {
                    # handle numbers
                    $searchNumber = (int) $searchTerm;
                    $numberNode = new EqNode($searchField, $searchNumber);
                    $orNode->addQuery($numberNode);
                }

                if ($this->isParsableDate($searchTerm)) {
                    # handle dates
                    $parsedDate = new \DateTime($searchTerm);
                    $searchDate = $parsedDate->format('Y-m-d');

                    $dateNode = new AndNode();
                    $searchFrom = new MongoDate(strtotime($searchDate." 00:00:00"));
                    $searchTo = new MongoDate(strtotime($searchDate." 23:59:59"));
                    $dateFrom = new GeNode($searchField, $searchFrom);
                    $dateTo = new LeNode($searchField, $searchTo);

                    $dateNode->addQuery($dateFrom);
                    $dateNode->addQuery($dateTo);
                    $orNode->addQuery($dateNode);
                }
            }
        }

        if (sizeof($orNode->getQueries()) > 0) {
            return $orNode;
        } else {
            return $searchNode;
        }
    }

    /**
     * Check Query for search nodes and translate them into corresponding like nodes
     *
     * @param AbstractNode $query        Query to translate
     * @param array        $searchFields Which fields should be searched for all terms in SearchNode
     * @return Query
     */
    public function translateSearchQuery(AbstractNode $query, $searchFields = array())
    {
        if (!($query instanceof Query)) {
            return $query;
        }

        $innerQuery = $query->getQuery();

        if ($innerQuery instanceof SearchNode) {
            $newNode = $this->translateSearchNode($innerQuery, $searchFields);

            if ($newNode instanceof OrNode) {
                $query->setQuery($newNode);
            }
        } elseif ($innerQuery instanceof AndNode) {
            $andNodeReplacement = new AndNode();
            foreach ($innerQuery->getQueries() as $innerNodeFromAnd) {

                if ($innerNodeFromAnd instanceof SearchNode) {
                    // Transform to OrNode with inner like queries and add to new query list
                    $andNodeReplacement->addQuery(
                        $this->translateSearchNode($innerNodeFromAnd, $searchFields)
                    );
                } else {
                    // Just recollect the node
                    $andNodeReplacement->addQuery($innerNodeFromAnd);
                }

            }

            $query->setQuery($andNodeReplacement);
        }

        return $query;
    }

    /**
     * Check if string can be parsed to date
     *
     * @param string $dateString The date string to be parsed
     * @return bool
     */
    protected function isParsableDate($dateString)
    {
        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            // Expected here, go on
            return false;
        }
        return true;
    }
}
