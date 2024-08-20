<?php
/**
 * SolrQuery class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\RestBundle\Service\RestServiceLocator;
use Graviton\Rql\Node\SearchNode;
use Psr\Cache\CacheItemPoolInterface;
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
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $urlParts = [];

    /**
     * @var array
     */
    private array $defaultSettings = [];

    /**
     * @var RestServiceLocator
     */
    private RestServiceLocator $restServiceLocator;

    /**
     * @var array
     */
    private array $solrExtraParams;

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

    private array $partPatterns;

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

    public const STRING EXTRA_PARAM_WILDCARD_BRIDGE = "WILDCARD_BRIDGE";
    public const STRING EXTRA_PARAM_FUZZY_BRIDGE = "FUZZY_BRIDGE";
    public const STRING EXTRA_PARAM_LITERAL_BRIDGE = "LITERAL_BRIDGE";
    public const STRING EXTRA_PARAM_ANDIFY_TERMS = "ANDIFY_TERMS";
    public const STRING EXTRA_PARAM_WEIGHTS = "WEIGHTS";

    /**
     * Constructor
     *
     * @param LoggerInterface        $logger                 logger
     * @param CacheItemPoolInterface $cache                  cache
     * @param string                 $solrUrl                url to solr
     * @param int                    $solrFuzzyBridge        fuzzy bridge
     * @param int                    $solrWildcardBridge     wildcard bridge
     * @param int                    $solrLiteralBridge      literal bridge
     * @param boolean                $andifyTerms            andify terms or not?
     * @param RestServiceLocator     $restServiceLocator     rest service locator
     * @param array                  $solrExtraParams        extra params
     * @param int                    $paginationDefaultLimit default pagination limit
     * @param Client                 $solrClient             solr client
     * @param RequestStack           $requestStack           request stack
     */
    public function __construct(
        LoggerInterface $logger,
        CacheItemPoolInterface $cache,
        $solrUrl,
        $solrFuzzyBridge,
        $solrWildcardBridge,
        $solrLiteralBridge,
        $andifyTerms,
        RestServiceLocator $restServiceLocator,
        array $solrExtraParams,
        $paginationDefaultLimit,
        Client $solrClient,
        RequestStack $requestStack
    ) {
        $this->logger = $logger;
        if (!is_null($solrUrl)) {
            $this->urlParts = parse_url($solrUrl);
        }

        $this->cache = $cache;

        $this->defaultSettings[self::EXTRA_PARAM_FUZZY_BRIDGE] = (int) $solrFuzzyBridge;
        $this->defaultSettings[self::EXTRA_PARAM_WILDCARD_BRIDGE] = (int) $solrWildcardBridge;
        $this->defaultSettings[self::EXTRA_PARAM_LITERAL_BRIDGE] = (int) $solrLiteralBridge;
        $this->defaultSettings[self::EXTRA_PARAM_ANDIFY_TERMS] = (bool) $andifyTerms;

        $this->restServiceLocator = $restServiceLocator;
        $this->solrExtraParams = $solrExtraParams;
        $this->paginationDefaultLimit = (int) $paginationDefaultLimit;
        $this->solrClient = $solrClient;
        $this->requestStack = $requestStack;

        // these are the patterns we recognize in the full query and replace with other stuff
        $this->partPatterns = [
            'ch-tel-no-prefix' => [
                'pattern' => '\d{3} \d{3} \d{2} \d{2}', // pattern without end/beginning!!
                'cleanup' => function ($input) {
                    $fullMatch = $input[0];

                    // remove trailing 0
                    if (str_starts_with($fullMatch, '0')) {
                        $fullMatch = substr($fullMatch, 1);
                    }

                    return '"+41'.str_replace(' ', '', $fullMatch).'"';
                }
            ],
            'tel-int-but-spaces-prefix' => [
                'pattern' => '\+?\d{1,3} \d{2,3} \d{2,3} \d{2,3} \d{2,3}',
                'cleanup' => function ($input) {
                    $fullMatch = $input[0];

                    return '"'.str_replace(' ', '', $fullMatch).'"';
                }
            ],
            'account-nr' => [
                'pattern' => '[0-9]{0,3}+ [0-9\.]{9,}',
                'cleanup' => function ($input) {
                    return '"'.$input[0].'"';
                }
            ],
            'solr-field-query' => [
                'pattern' => '[\S]{2,}:[\S]{1,}',
                'cleanup' => function ($input) {
                    $fieldNameParts = explode(':', $input[0]);
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
            ],
            'normal-field' => [
                'pattern' => '[\S]+',
                'cleanup' => function ($input) {
                    return trim($this->getSingleTerm($input[0]));
                }
            ]
        ];
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
        if (!empty($this->urlParts) && $this->hasSolr($this->className)) {
            return true;
        }
        return false;
    }

    /**
     * returns if solr is configured for the given classname
     *
     * @param string $className class name
     *
     * @return bool true if yes
     */
    public function hasSolr($className) : bool
    {
        $model = $this->restServiceLocator->getDocumentModel($className);
        if (is_null($model)) {
            return false;
        }

        return !empty($model->getRuntimeDefinition()->getSolrFields());
    }

    /**
     * get setting
     *
     * @param string $settingName setting name
     *
     * @return mixed value
     */
    public function getSetting(string $settingName) : mixed
    {
        $checkName = strtoupper($this->getCoreFromClassName($this->className));
        if (!empty($this->solrExtraParams[$checkName][$settingName])) {
            return $this->solrExtraParams[$checkName][$settingName];
        }
        if (isset($this->defaultSettings[$settingName])) {
            return $this->defaultSettings[$settingName];
        }
        return null;
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

        $weightString = $this->getSolrWeightString($this->className);

        // set the weights
        $query->getEDisMax()->setQueryFields($weightString);

        $searchTerm = $this->getSearchTerm($node);
        $query->setQuery($searchTerm);

        if ($limitNode instanceof LimitNode) {
            $query->setStart($limitNode->getOffset())->setRows($limitNode->getLimit());
        } else {
            $query->setStart(0)->setRows($this->paginationDefaultLimit);
        }

        // sort?
        $checkName = strtoupper($this->getCoreFromClassName($this->className));
        if (!empty($this->solrExtraParams[$checkName])) {
            foreach ($this->solrExtraParams[$checkName] as $param => $value) {
                if (!in_array($param, array_keys($this->defaultSettings)) && $param != self::EXTRA_PARAM_WEIGHTS) {
                    $query->addParam(
                        strtolower($param),
                        $value
                    );
                }
            }
        }

        $this->logger->info(
            'Executing solr search',
            [
                'query' => $searchTerm,
                'fields' => $weightString,
                'params' => $query->getParams(),
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

        $knownPatterns = $this->scanForKnownPatterns($fullTerm);
        $glue = $this->getSetting(self::EXTRA_PARAM_ANDIFY_TERMS) ? '&&' : '';

        $searchExpression = '';

        if (!is_array($knownPatterns['found'])) {
            return $searchExpression;
        }

        $i = 1;
        $numberOfMatches = count($knownPatterns['found']);
        foreach ($knownPatterns['found'] as $match) {
            $searchExpression .= $match['matching'].' ';

            if (!empty($match['operator'])) {
                $searchExpression .= $match['operator'].' ';
            } else {
                if ($i < $numberOfMatches && !empty($glue)) {
                    $searchExpression .= $glue.' ';
                }
            }

            $i++;
        }

        return trim($searchExpression);
    }

    /**
     * scan for stuff we know and return it, removing from fullTerm,
     *
     * @param string $input input
     *
     * @return array parsed things
     */
    private function scanForKnownPatterns($input) : array
    {
        // get operator part!
        $operatorPatterns = implode('|', array_map('preg_quote', $this->queryOperators));
        $foundPatterns = [];

        $oneRoundNoMatch = false;
        while (!$oneRoundNoMatch) {
            $matched = false;
            foreach ($this->partPatterns as $name => $part) {
                // complete pattern!
                $pattern = "/^" . $part['pattern'] . "[\s]*(" . $operatorPatterns . ")?/i";

                preg_match($pattern, trim($input), $match);

                if (empty($match)) {
                    continue;
                }

                // remove from input!
                $input = trim(str_replace($match[0], '', $input));

                // extract operator if present!
                $lastElement = trim(array_pop($match));
                $queryOperator = null;
                // is it operator?
                if (in_array($lastElement, $this->queryOperators)) {
                    $queryOperator = $lastElement;

                    // remove from whole match!
                    if (str_ends_with($match[0], $queryOperator)) {
                        $match[0] = trim(substr($match[0], 0, strlen($queryOperator) * -1));
                    }
                } else {
                    // no operator, put back!
                    $match[] = $lastElement;
                }

                // cleaner?
                if (isset($part['cleanup']) && is_callable($part['cleanup'])) {
                    $matching = $part['cleanup']($match);
                } else {
                    $matching = $match[0];
                }

                // change operator
                if ($queryOperator == '-') {
                    $queryOperator = "NOT";
                }

                $foundPatterns[] = [
                    'matching' => $matching,
                    'operator' => $queryOperator
                ];

                // we matched something in this iteration. start from the top again!
                $matched = true;
                break;
            }

            // one round not matched?
            if (!$matched) {
                $oneRoundNoMatch = true;
            }
        }

        return [
            'found' => $foundPatterns,
            'remaining' => trim($input)
        ];
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
        // is it a unescaped literal metachar?
        $metachars = ['&', '+'];
        if (in_array($term, $metachars)) {
            return sprintf('(\\%s)', $term);
        }

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
        if (strlen($term) > 3 && preg_match_all($pattern, $term, $matches) === 1) {
            return '"'.$term.'"';
        }

        // strings shorter then 5 chars (like hans) we wildcard, all others we make fuzzy
        if (strlen($term) >= $this->getSetting(self::EXTRA_PARAM_FUZZY_BRIDGE)) {
            return $this->doAndNotPrefixSingleTerm($term, '~');
        }

        if (strlen($term) >= $this->getSetting(self::EXTRA_PARAM_WILDCARD_BRIDGE)) {
            return $this->doAndNotPrefixSingleTerm($term, '*');
        }

        return $term;
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
        if (strlen($originalTerm) >= $this->getSetting(self::EXTRA_PARAM_LITERAL_BRIDGE) && $originalTerm != $term) {
            // case when $originalTerm contains some characters, we want to quote it in original form!
            $quoteOnContaining = ['-']; // "-" for jean-pierre!
            $shouldQuoteOriginal = false;
            foreach ($quoteOnContaining as $char) {
                if (str_contains($originalTerm, $char)) {
                    $shouldQuoteOriginal = true;
                    break;
                }
            }

            if (str_starts_with($originalTerm, "\"") && str_ends_with($originalTerm, "\"")) {
                $shouldQuoteOriginal = false;
            }

            return sprintf(
                '(%s || %s)',
                $shouldQuoteOriginal ? '"'.$originalTerm.'"' : $originalTerm,
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
        $endpointConfig['core'] = $this->getCoreFromClassName($this->className);

        $endpointConfig['timeout'] = 10000;
        $endpointConfig['key'] = 'local';

        $this->solrClient->addEndpoint($endpointConfig);
        $this->solrClient->setDefaultEndpoint($endpointConfig['key']);

        return $this->solrClient;
    }

    /**
     * full class name
     *
     * @param string $className full class name
     * @return void
     */
    private function getCoreFromClassName(string $className) : string
    {
        $classnameParts = explode('\\', $className);
        return array_pop($classnameParts);
    }

    /**
     * Returns the solr weight string
     *
     * @param string $className class name
     *
     * @return string weight string
     */
    private function getSolrWeightString(string $className) : string
    {
        $cacheKey = 'SOLR-WEIGHTSTRING-'.$this->getCoreFromClassName($className);
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $weightSetting = $this->getSetting(self::EXTRA_PARAM_WEIGHTS);

            // custom weights provided?
            $customWeights = [];
            if (!empty($weightSetting)) {
                $weights = explode(' ', $weightSetting);
                foreach ($weights as $fullWeight) {
                    $singleWeight = explode('^', $fullWeight);
                    if (count($singleWeight) != 2) {
                        continue;
                    }
                    $customWeights[$singleWeight[0]] = $fullWeight;
                }
            }

            // compose the final string!
            $finalWeights = [];
            $solrFields = $this->restServiceLocator
                ->getDocumentModel($className)
                ->getRuntimeDefinition()
                ->getSolrFields();

            foreach ($solrFields as $field) {
                if (is_numeric($field['weight']) && $field['weight'] != 0) {
                    $finalWeights[$field['name']] = $field['name'].'^'.$field['weight'];
                }
            }

            // custom overrides
            foreach ($customWeights as $fieldName => $fieldWeight) {
                $finalWeights[$fieldName] = $fieldWeight;
            }

            $cacheItem->set(implode(' ', $finalWeights));
            $this->cache->save($cacheItem);
            return $cacheItem->get();
        }

        return $cacheItem->get();
    }
}
