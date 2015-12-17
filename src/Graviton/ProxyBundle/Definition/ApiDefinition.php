<?php
/**
 * Store all necessary information about 3rd party APIs
 */

namespace Graviton\ProxyBundle\Definition;

/**
 * ApiDefinition
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class ApiDefinition
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $host;

    /**
     * @var mixed
     */
    private $origin;

    /**
     * @var array
     */
    private $endpoints = array();

    /**
     * @var array
     */
    private $schemes = array();

    /**
     * sets the base path of the api
     *
     * @param string $basePath API base path
     *
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * sets the FQDN of the API
     *
     * @param string $host FQDN
     *
     * @return void
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * get the FQDN of the API
     *
     * @return string FQDN
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * set the origin service definition
     *
     * @param mixed $origin the origin service definition (type depends on dispersal strategy)
     *
     * @return void
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * get the origin service definition
     *
     * @return mixed the origin service definition (type depends on dispersal strategy)
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * add an endpoint
     *
     * @param string $endpoint endpoint
     *
     * @return void
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoints[] = $endpoint;
    }

    /**
     * check if an endpoint exists
     *
     * @param string $endpoint endpoint
     *
     * @return boolean
     */
    public function hasEndpoint($endpoint)
    {
        $retVal = false;
        if (isset($this->endpoints)) {
            $retVal = in_array($endpoint, $this->endpoints);
        }

        return $retVal;
    }

    /**
     * get all defined API endpoints
     *
     * @param boolean $withHost url with hostname
     * @param string  $prefix   add a prefix to the url (blub/endpoint/url)
     *
     * @return array
     */
    public function getEndpoints($withHost = true, $prefix = null)
    {
        $endpoints = array();
        $basePath = "";
        if ($withHost) {
            $basePath = $this->getHost();
        }
        if (!empty($prefix)) {
            $basePath .= $prefix;
        }
        if (isset($this->basePath)) {
            $basePath .= $this->basePath;
        }
        foreach ($this->endpoints as $endpoint) {
            $endpoints[] = $basePath.$endpoint;
        }

        return $endpoints;
    }

    /**
     * add a schema for an endpoint
     *
     * @param string    $endpoint endpoint
     * @param \stdClass $schema   schema
     *
     * @return void
     */
    public function addSchema($endpoint, $schema)
    {
        $this->schemes[$endpoint] = $schema;
    }

    /**
     * get a schema for an endpoint
     *
     * @param string $endpoint endpoint
     *
     * @return \stdClass
     */
    public function getSchema($endpoint)
    {
        //remove base path
        if ('/' !== $this->basePath) {
            $endpoint = str_replace($this->basePath, '', $endpoint);
        }
        $retVal = new \stdClass();
        if (array_key_exists($endpoint, $this->schemes)) {
            $retVal = $this->schemes[$endpoint];
        }

        return $retVal;
    }
}
