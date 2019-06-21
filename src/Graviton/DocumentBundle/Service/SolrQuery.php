<?php
/**
 * SolrQuery class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\Rql\Node\SearchNode;
use Solarium\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Graviton\RqlParser\Node\LimitNode;

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
     * @var int
     */
    private $solrWildcardBridge;

    /**
     * @var boolean
     */
    private $andifyTerms;

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
     * if the full search term matches one of these patterns, the whole thing is sent quoted to solr
     *
     * @var array
     */
    private $fullTermPatterns = [
        '/^[0-9]+ [0-9\.]{9,}$/i'
    ];

    /**
     * pattern to match a solr field query
     *
     * @var string
     */
    private $fieldQueryPattern = '/(.{2,}):(.+)/i';

    /**
     * stuff that does not get andified/quoted/whatever
     *
     * @var array
     */
    private $queryOperators = [
        'AND',
        'NOT',
        'OR',
        '&&',
        '||',
        '!',
        '-'
    ];

    /**
     * Constructor
     *
     * @param string       $solrUrl                url to solr
     * @param int          $solrFuzzyBridge        fuzzy bridge
     * @param int          $solrWildcardBridge     wildcard bridge
     * @param boolean      $andifyTerms            andify terms or not?
     * @param array        $solrMap                solr class field weight map
     * @param int          $paginationDefaultLimit default pagination limit
     * @param Client       $solrClient             solr client
     * @param RequestStack $requestStack           request stack
     */
    public function __construct(
        $solrUrl,
        $solrFuzzyBridge,
        $solrWildcardBridge,
        $andifyTerms,
        array $solrMap,
        $paginationDefaultLimit,
        Client $solrClient,
        RequestStack $requestStack
    ) {
        if (!is_null($solrUrl)) {
            $this->urlParts = parse_url($solrUrl);
        }
        $this->solrFuzzyBridge = (int) $solrFuzzyBridge;
        $this->solrWildcardBridge = (int) $solrWildcardBridge;
        $this->andifyTerms = (boolean) $andifyTerms;
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
            $this->requestStack->getCurrentRequest()->attributes->set('totalCount', $result->getNumFound());
            $this->requestStack->getCurrentRequest()->attributes->set('X-Search-Source', 'solr');
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
        $fullTerm = $node->getSearchQuery();

        foreach ($this->fullTermPatterns as $pattern) {
            if (preg_match($pattern, $fullTerm, $matches) === 1) {
                return '"'.$fullTerm.'"';
            }
        }

        if ($this->andifyTerms) {
            $glue = '&&';
        } else {
            $glue = '';
        }


        $i = 0;
        $hasPreviousOperator = false;
        $fullSearchElements = [];

        foreach (explode(' ', $node->getSearchQuery()) as $term) {
            $i++;

            // is this an operator?
            if (array_search($term, $this->queryOperators) !== false) {
                $fullSearchElements[] = $term;
                $hasPreviousOperator = true;
                continue;
            }

            $singleTerm = $this->getSingleTerm($term);

            if ($i > 1 && $hasPreviousOperator == false && !empty($glue)) {
                $fullSearchElements[] = $glue;
            } else {
                $hasPreviousOperator = false;
            }

            $fullSearchElements[] = $singleTerm;
        }

        return implode(' ', $fullSearchElements);
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
            return '"'.$term.'"';
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
            return '"'.$term.'"';
        }

        // everything that is only numbers *and* characters and at least 3 long, we don't fuzzy/wildcard
        // thanks to https://stackoverflow.com/a/7684859/3762521
        $pattern = '/^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]+)$/';
        if (strlen($term) > 3 && preg_match($pattern, $term, $matches) === 1) {
            return '"'.$term.'"';
        }

        // is it a solr field query (like id:333)?
        if (preg_match($this->fieldQueryPattern, $term) === 1) {
            return $this->parseSolrFieldQuery($term);
        }

        // strings shorter then 5 chars (like hans) we wildcard, all others we make fuzzy
        if (strlen($term) >= $this->solrFuzzyBridge) {
            return $this->doAndNotPrefixSingleTerm($term, '~');
        }

        if (strlen($term) >= $this->solrWildcardBridge) {
            return $this->doAndNotPrefixSingleTerm($term, '*');
        }

        return $term;
    }

    /**
     * parses the special solr field syntax fieldName:fieldValue, converts int ranges
     *
     * @param string $fieldQuery the query
     *
     * @return string solr compatible expression
     */
    private function parseSolrFieldQuery($fieldQuery)
    {
        $fieldNameParts = explode(':', $fieldQuery);
        $fieldName = $fieldNameParts[0];
        unset($fieldNameParts[0]);
        $fieldValue = implode(':', $fieldNameParts);

        // change > and <
        if ($fieldValue[0] == '<') {
            $fieldValue = '[* TO '.substr($fieldValue, 1).']';
        } elseif ($fieldValue[0] == '>') {
            $fieldValue = '['.substr($fieldValue, 1).' TO *]';
        } else {
            $fieldValue = $this->getSingleTerm($fieldValue);
        }

        return $fieldName.':'.$fieldValue;
    }

    /**
     * ORify a single term
     *
     * @param string $term     search term
     * @param string $modifier modified
     *
     * @return string ORified query
     */
    private function doAndNotPrefixSingleTerm($term, $modifier)
    {
        // already modifier there?
        $last = substr($term, -1);
        if ($last == '~' || $last == '*') {
            // clean from term, override modifier from client
            $modifier = $last;
            $term = substr($term, 0, -1);
        }

        return sprintf(
            '(%s || %s%s)',
            $term,
            $term,
            $modifier
        );
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
