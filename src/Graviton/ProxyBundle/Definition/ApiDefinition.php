<?php
/**
 * Store all necessary information about 3rd party APIs
 */

namespace Graviton\ProxyBundle\Definition;

/**
 * ApiDefinition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
     * @var array
     */
    private $endpoints;

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
     * add an endpoint
     *
     * @param string $endpoint endpoint
     *
     * @return void
     */
    public function addEndpoints($endpoint)
    {
        $this->endpoints[] = $endpoint;
    }
}
