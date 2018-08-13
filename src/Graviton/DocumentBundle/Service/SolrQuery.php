<?php
/**
 * SolrQuery class file
 */

namespace Graviton\DocumentBundle\Service;


use Graviton\Rql\Node\SearchNode;
use Solarium\Client;
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
     * @var array
     */
    private $solrMap;

    /**
     * Constructor
     *
     * @param string $dateFormat date format
     * @param string $timezone   timezone
     */
    public function __construct($solrUrl, array $solrMap)
    {
        if (!is_null($solrUrl)) {
            $this->urlParts = parse_url($solrUrl);
        }

        $this->solrMap = $solrMap;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function isConfigured()
    {
        if (isset($this->solrMap[$this->className]) && !empty($this->urlParts)) {
            return true;
        }
        return false;
    }

    public function query(SearchNode $node, LimitNode $limitNode = null)
    {
        $client = new Client([
            'endpoint' => array(
                'localhost' => $this->getUrlForCore()
            )
        ]);

        $query = $client->createQuery($client::QUERY_SELECT);
        $query->setQuery('müller');

        $query->setStart(2)->setRows(20);
        $query->setFields(array('id'));
        //$query->addSort('price', $query::SORT_ASC);

        $resultset = $client->select($query);

        foreach ($resultset as $document) {
            var_dump($document);
        }

        echo "juhuu"; die;
    }

    private function getUrlForCore()
    {
        $parts = $this->urlParts;
        if (!isset($parts['path'])) {
            $parts['path'] = '/';
        }

        if (substr($parts['path'], -1) != '/') {
            $parts['path'] .= '/';
        }

        // append core name - derived from classname
        $classnameParts = explode('\\', $this->className);
        $parts['path'] .= array_pop($classnameParts);

        return $parts;
    }

}
