<?php
/**
 * ParamConverter class for entry point to ProxyApi Bundle
 */

namespace Graviton\ProxyApiBundle\Manager;

use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ProxyApiBundle\Helper\HttpHelper;
use Graviton\ProxyApiBundle\Listener\ProxyExceptionListener;
use Graviton\ProxyApiBundle\Model\ProxyModel;
use Graviton\ProxyApiBundle\Processor\PostProcessorInterface;
use Graviton\ProxyApiBundle\Processor\PreProcessor;
use Graviton\ProxyApiBundle\Processor\PreProcessorInterface;
use Graviton\ProxyApiBundle\Processor\ProxyProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service Request Converter and startup for ProxyApi
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ServiceManager
{
    /** TODO Cache name for services */
    const CACHE_KEY_SERVICES = 'proxy_services';
    const CACHE_KEY_SERVICES_TIME = 10;
    const CACHE_KEY_SERVICES_URLS = 'proxy_services_urls';
    const CACHE_KEY_SERVICES_URLS_TIME = 10;
    const CACHE_KEY_SERVICES_PREFIX = 'proxy_';

    /** @var Request */
    protected $request;

    /** @var ProxyManager */
    protected $proxyManager;

    /** @var CacheProvider */
    protected $cacheProvider;

    /** @var HttpHelper */
    protected $httpHelper;

    /**
     * Client request "source"
     *
     * @var string
     */
    protected $reqClient;

    /**
     * Client request service
     *
     * @var string
     */
    protected $reqService;

    /** @var PreProcessor */
    protected $preProcessor;
    protected $proxyProcessor;
    protected $postProcessor;

    /**
     * ServiceConverter constructor.
     * @param RequestStack  $requestStack  Sf Request information service
     * @param HttpHelper    $httpHelper    Request http builder
     * @param ProxyManager  $proxyManager  Db Manager and query control
     * @param CacheProvider $cacheProvider Cache service
     */
    public function __construct(
        RequestStack $requestStack,
        HttpHelper $httpHelper,
        ProxyManager $proxyManager,
        CacheProvider $cacheProvider
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpHelper = $httpHelper;
        $this->proxyManager = $proxyManager;
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * Get Request Proxy model
     *
     * @return ProxyModel
     */
    private function getProxyModel()
    {
        $this->reqClient  = $this->request->get('client');
        $this->reqService = $this->request->get('service');

        if (!$this->reqClient) {
            throw new ProxyExceptionListener(404, 'Proxy service not found');
        }

        /** @var ProxyModel $proxyModel */
        $proxyModel = $this->proxyManager->getService($this->reqClient);

        return $proxyModel;
    }

    /**
     * List all services
     * TODO correct link
     *
     * @return array
     */
    public function getServices()
    {
        $services = [];
        foreach ($this->proxyManager->getServices()['services'] as $key => $service) {
            $services[$key] = '/'.$key;
        }

        return $services;
    }

    /**
     * Execute proxy request
     *
     * @return Response
     */
    public function processRequest()
    {
        // Check if there is a service with that name
        $proxyModel = $this->getProxyModel();

        $this->httpHelper->setBaseUri($proxyModel->getUri());

        // Do processing steps
        $preProcessing = $proxyModel->getPreProcessorService();
        if (!$preProcessing instanceof PreProcessorInterface) {
            throw new ProxyExceptionListener(412, 'Configured PreProcessing configuration is incorrect');
        }
        $this->httpHelper = $preProcessing->process($this->request, $this->httpHelper, $proxyModel);

        $proxyProcesor = $proxyModel->getProxyProcessorService();
        if (!$proxyProcesor instanceof ProxyProcessorInterface) {
            throw new ProxyExceptionListener(412, 'Configured ProxyProcessor configuration is incorrect');
        }

        /** @var Response $response */
        $response = $proxyProcesor->process($this->request, $this->httpHelper, $proxyModel);

        $postProcesor = $proxyModel->getPostProcessorService();
        if (!$postProcesor instanceof PostProcessorInterface) {
            throw new ProxyExceptionListener(412, 'Configured ProxyProcessor configuration is incorrect');
        }
        $response = $postProcesor->process($response, $proxyModel);

        return $response;
    }
}
