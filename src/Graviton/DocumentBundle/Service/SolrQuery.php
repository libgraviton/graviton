<?php
/**
 * SolrQuery class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\Rql\Node\SearchNode;
use Psr\Log\LoggerInterface;
use Solarium\Core\Client\Client;
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
     * @var LoggerInterface
     */
    private $logger;

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
    private int $solrFuzzyBridge;

    /**
     * @var int
     */
    private int $solrWildcardBridge;

    /**
     * @var int
     */
    private int $solrLiteralBridge;

    /**
     * @var boolean
     */
    private bool $andifyTerms;

    /**
     * @var array
     */
    private array $solrMap;

    /**
     * @var array
     */
    private array $solrMapSort;

    /**
     * @var int
     */
    private int $paginationDefaultLimit;

    /**
     * @var Client
     */
    private $solrClient;

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * if the full search term matches one of these patterns, the whole thing is sent quoted to solr
     *
     * @var array
     */
    private array $fullTermPatterns = [
        '/^[0-9]+ [0-9\.]{9,}$/i'
    ];

    /**
     * pattern to match a solr field query
     *
     * @var string
     */
    private string $fieldQueryPattern = '/(.{2,}):(.+)/i';

    /**
     * stuff that does not get andified/quoted/whatever
     *
     * @var array
     */
    private array $queryOperators = [
        'AND',
        'NOT',
        'OR',
        '&&',
        '||',
        '!',
        '-'
    ];

    private array $metaCharacters = [
        '-'
    ];

    /**
     * Constructor
     *
     * @param LoggerInterface $logger                 logger
     * @param string          $solrUrl                url to solr
     * @param int             $solrFuzzyBridge        fuzzy bridge
     * @param int             $solrWildcardBridge     wildcard bridge
     * @param int             $solrLiteralBridge      literal bridge
     * @param boolean         $andifyTerms            andify terms or not?
     * @param array           $solrMap                solr class field weight map
     * @param array           $solrMapSort            solr class field sort map
     * @param int             $paginationDefaultLimit default pagination limit
     * @param Client          $solrClient             solr client
     * @param RequestStack    $requestStack           request stack
     */
    public function __construct(
        LoggerInterface $logger,
        $solrUrl,
        $solrFuzzyBridge,
        $solrWildcardBridge,
        $solrLiteralBridge,
        $andifyTerms,
        array $solrMap,
        array $solrMapSort,
        $paginationDefaultLimit,
        Client $solrClient,
        RequestStack $requestStack
    ) {
        $this->logger = $logger;
        if (!is_null($solrUrl)) {
            $this->urlParts = parse_url($solrUrl);
        }
        $this->solrFuzzyBridge = (int) $solrFuzzyBridge;
        $this->solrWildcardBridge = (int) $solrWildcardBridge;
        $this->solrLiteralBridge = (int) $solrLiteralBridge;
        $this->andifyTerms = (boolean) $andifyTerms;
        $this->solrMap = $solrMap;
        $this->solrMapSort = $solrMapSort;
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
        $queryFields = $this->solrMap[$this->className];
        $query->getEDisMax()->setQueryFields($queryFields);

        $searchTerm = $this->getSearchTerm($node);
        $query->setQuery($searchTerm);

        if ($limitNode instanceof LimitNode) {
            $query->setStart($limitNode->getOffset())->setRows($limitNode->getLimit());
        } else {
            $query->setStart(0)->setRows($this->paginationDefaultLimit);
        }

        // sort?
        if (!empty($this->solrMapSort[$this->className])) {
            $query->addParam(
                'sort',
                $this->solrMapSort[$this->className]
            );
        }

        $this->logger->info(
            'Executing solr search',
            [
                'fields' => $queryFields,
                'query' => $searchTerm,
                'start' => $query->getStart(),
                'rows' => $query->getRows()
            ]
        );

        $query->setFields(['id']);

        $result = $client->select($query);

        if ($this->requestStack->getCurrentRequest() instanceof Request) {
            $this->requestStack->getCurrentRequest()->attributes->set('totalCount', $result->getNumFound());
            $this->requestStack->getCurrentRequest()->attributes->set('X-Search-Source', 'solr');
        }

        $this->logger->info(
            'Finished solr search',
            [
                'resultCount' => $result->getNumFound()
            ]
        );

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

        // split and drop empty terms
        $terms = array_filter(
            explode(' ', $fullTerm),
            function ($term) {
                return !empty($term);
            }
        );

        $i = 0;
        $hasPreviousOperator = false;
        $fullSearchElements = [];

        foreach ($terms as $term) {
            $i++;

            // is this an operator?
            if (in_array($term, $this->queryOperators)) {
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
        // booleans
        if ($term == 'true') {
            return 'T';
        } elseif ($term == 'false') {
            return 'F';
        }

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
        // put this aside
        $originalTerm = $term;

        // already modifier there?
        $last = substr($term, -1);
        if (str_ends_with($term, '~') || str_ends_with($term, '*')) {
            // clean from term, override modifier from client
            $modifier = $last;
            $term = substr($term, 0, -1);
            $originalTerm = $term;
        }

        // in case of wildcard (modifier == '*'), we have 2 modes: normal and regex
        // regex we use if the term contains any characters included in $this->metaCharacters
        $hasMetaCharacter = false;
        if ($modifier == '*') {
            foreach ($this->metaCharacters as $character) {
                if (strpos($term, $character) > 0) {
                    $term = str_replace($character, '[' . $character . ']', $term);
                    $hasMetaCharacter = true;
                }
            }
        }

        // change to regex if metacharacter or normal expr if not
        if ($hasMetaCharacter) {
            $term = sprintf('/%s.*/', $term);
        } else {
            $term = sprintf('%s%s', $term, $modifier);
        }

        // only do full term if length gte literalBridge
        if (strlen($originalTerm) >= $this->solrLiteralBridge && $originalTerm != $term) {
            return sprintf(
                '(%s || %s)',
                $originalTerm,
                $term
            );
        } else {
            return sprintf(
                '(%s)',
                $term
            );
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

        if (!str_ends_with($endpointConfig['path'], '/')) {
            $endpointConfig['path'] .= '/';
        }

        // for solarium >5 -> strip "solr/" from path if it exists
        $stripPath = 'solr/';
        if (strlen($endpointConfig['path']) > strlen($stripPath) &&
            str_ends_with($endpointConfig['path'], $stripPath)
        ) {
            $endpointConfig['path'] = substr(
                $endpointConfig['path'],
                0,
                strlen($endpointConfig['path']) - strlen($stripPath)
            );
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
