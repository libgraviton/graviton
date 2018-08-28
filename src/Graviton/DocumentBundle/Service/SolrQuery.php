<?php
/**
 * SolrQuery class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\Rql\Node\SearchNode;
use Solarium\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Xiag\Rql\Parser\Node\LimitNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrQuery
{

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $urlParts = [];

    /**
     * @var int
     */
    private $solrFuzzyBridge;

    /**
     * @var array
     */
    private $solrMap;

    /**
     * @var int
     */
    private $paginationDefaultLimit;

    /**
     * @var Client
     */
    private $solrClient;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor
     *
     * @param string       $solrUrl                url to solr
     * @param int          $solrFuzzyBridge        fuzzy bridge
     * @param array        $solrMap                solr class field weight map
     * @param int          $paginationDefaultLimit default pagination limit
     * @param Client       $solrClient             solr client
     * @param RequestStack $requestStack           request stack
     */
    public function __construct(
        $solrUrl,
        $solrFuzzyBridge,
        array $solrMap,
        $paginationDefaultLimit,
        Client $solrClient,
        RequestStack $requestStack
    ) {
        if (!is_null($solrUrl)) {
            $this->urlParts = parse_url($solrUrl);
        }

        $this->solrFuzzyBridge = (int) $solrFuzzyBridge;
        $this->solrMap = $solrMap;
        $this->paginationDefaultLimit = (int) $paginationDefaultLimit;
        $this->solrClient = $solrClient;
        $this->requestStack = $requestStack;
    }

    /**
     * sets the class name to search - last part equates to solr core name
     *
     * @param string $className class name
     *
     * @return void
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * returns true if solr is configured currently, false otherwise
     *
     * @return bool if solr is configured
     */
    public function isConfigured()
    {
        if (!empty($this->urlParts) && isset($this->solrMap[$this->className])) {
            return true;
        }
        return false;
    }

    /**
     * executes the search on solr using the rql parsing nodes.
     *
     * @param SearchNode     $node      search node
     * @param LimitNode|null $limitNode limit node
     *
     * @return array an array of just record ids (the ids of the matching documents in solr)
     */
    public function query(SearchNode $node, LimitNode $limitNode = null)
    {
        $client = $this->getClient();

        $query = $client->createQuery($client::QUERY_SELECT);

        // set the weights
        $query->getEDisMax()->setQueryFields($this->solrMap[$this->className]);

        $query->setQuery($this->getSearchTerm($node));

        if ($limitNode instanceof LimitNode) {
            $query->setStart($limitNode->getOffset())->setRows($limitNode->getLimit());
        } else {
            $query->setStart(0)->setRows($this->paginationDefaultLimit);
        }

        $query->setFields(['id']);

        $result = $client->select($query);

        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            $this->requestStack->getCurrentRequest()->attributes->set('solr-total-count', $result->getNumFound());
        }

        $idList = [];
        foreach ($result as $document) {
            if (isset($document->id)) {
                $idList[] = (string) $document->id;
            } elseif (isset($document->_id)) {
                $idList[] = (string) $document->_id;
            }
        }

        return $idList;
    }

    /**
     * returns the string search term to be used in the solr query
     *
     * @param SearchNode $node the search node
     *
     * @return string the composed search query
     */
    private function getSearchTerm(SearchNode $node)
    {
        return implode(
            ' ',
            array_map([$this, 'getSingleTerm'], $node->getSearchTerms())
        );
    }

    /**
     * returns a single term how to search. here we can apply custom logic to the user input string
     *
     * @param string $term single search term
     *
     * @return string modified search term
     */
    private function getSingleTerm($term)
    {
        // we don't modify numbers
        if (ctype_digit($term)) {
            return $term;
        }

        // formatted number?
        $formatted = str_replace(
            [
                '-',
                '.'
            ],
            '',
            $term
        );
        if (ctype_digit($formatted)) {
            return $term;
        }

        // strings shorter then 5 chars (like hans) we wildcard, all others we make fuzzy
        if (strlen($term) < $this->solrFuzzyBridge) {
            return $term . '*';
        } else {
            return $term . '~';
        }
    }

    /**
     * returns the client to use for the current query
     *
     * @return Client client
     */
    private function getClient()
    {
        $endpointConfig = $this->urlParts;
        if (!isset($endpointConfig['path'])) {
            $endpointConfig['path'] = '/';
        }

        if (substr($endpointConfig['path'], -1) != '/') {
            $endpointConfig['path'] .= '/';
        }

        // find core name
        $classnameParts = explode('\\', $this->className);
        $endpointConfig['core'] = array_pop($classnameParts);

        $endpointConfig['timeout'] = 10000;
        $endpointConfig['key'] = 'local';

        $this->solrClient->addEndpoint($endpointConfig);
        $this->solrClient->setDefaultEndpoint($endpointConfig['key']);

        return $this->solrClient;
    }
}
