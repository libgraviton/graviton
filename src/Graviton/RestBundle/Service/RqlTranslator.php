<?php
/**
 * Translator for RQL modification before execution
 */

namespace Graviton\RestBundle\Service;

use Graviton\Rql\Node\SearchNode;
use Xiag\Rql\Parser\AbstractNode;
use Xiag\Rql\Parser\DataType\Glob;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
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
}
