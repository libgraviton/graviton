<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\ProxyApiBundle\Model;

use Graviton\ProxyApiBundle\Processor\PostProcessorInterface;
use Graviton\ProxyApiBundle\Processor\PreProcessorInterface;
use Graviton\ProxyApiBundle\Processor\ProxyProcessorInterface;

/**
 * Schema
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProxyModel
{
    protected $name = '';
    protected $uri = '';
    protected $serviceEndpoint = '';
    protected $queryAdditionals = [];
    protected $queryParams = [];

    /** @var PreProcessorInterface */
    protected $preProcessorService;
    protected $proxyProcessorService;
    protected $postProcessorService;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name setter
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri setter
     * @return void
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getServiceEndpoint()
    {
        return $this->serviceEndpoint;
    }

    /**
     * @param mixed $serviceEndpoint setter
     * @return void
     */
    public function setServiceEndpoint($serviceEndpoint)
    {
        $this->serviceEndpoint = $serviceEndpoint;
    }

    /**
     * Object Processor
     *
     * @return PreProcessorInterface setter
     */
    public function getPreProcessorService()
    {
        return $this->preProcessorService;
    }

    /**
     * @param PreProcessorInterface $preProcessorService setter
     * @return void
     */
    public function setPreProcessorService(PreProcessorInterface $preProcessorService)
    {
        $this->preProcessorService = $preProcessorService;
    }

    /**
     * Object Processor
     *
     * @return ProxyProcessorInterface
     */
    public function getProxyProcessorService()
    {
        return $this->proxyProcessorService;
    }

    /**
     * @param ProxyProcessorInterface $proxyProcessorService setter
     * @return void
     */
    public function setProxyProcessorService($proxyProcessorService)
    {
        $this->proxyProcessorService = $proxyProcessorService;
    }

    /**
     * Object Processor
     *
     * @return PostProcessorInterface
     */
    public function getPostProcessorService()
    {
        return $this->postProcessorService;
    }

    /**
     * @param PostProcessorInterface $postProcessorService setter
     * @return void
     */
    public function setPostProcessorService($postProcessorService)
    {
        $this->postProcessorService = $postProcessorService;
    }

    /**
     * @return array
     */
    public function getQueryAdditionals()
    {
        return $this->queryAdditionals;
    }

    /**
     * @param array $queryAdditionals Optional added query params
     * @return void
     */
    public function setQueryAdditionals($queryAdditionals)
    {
        $this->queryAdditionals = $queryAdditionals;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param array $queryParams Unique request params
     * @return void
     */
    public function setQueryParams($queryParams)
    {
        $this->queryParams = $queryParams;
    }
}
