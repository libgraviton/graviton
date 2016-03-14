<?php
/**
 * Translator for RQL modification before execution
 */

namespace Graviton\RestBundle\Service;

use Graviton\Rql\Node\SearchNode;
use Xiag\Rql\Parser\DataType\Glob;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\LikeNode;

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
}
