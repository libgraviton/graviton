<?php
/**
 * controller for start page
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\CoreBundle\Event\HomepageRenderEvent;
use Graviton\ProxyBundle\Service\ApiDefinitionLoader;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * MainController
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MainController
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var RestUtilsInterface
     */
    private $restUtils;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ApiDefinitionLoader
     */
    private $apiLoader;

    /**
     * @var array
     */
    private $addditionalRoutes;

    /**
     * @var array
     */
    private $pathWhitelist;

    /**
     * @var array
     */
    private $proxySourceConfiguration;

    /**
     * @param Router                   $router                   router
     * @param Response                 $response                 prepared response
     * @param RestUtilsInterface       $restUtils                rest-utils from GravitonRestBundle
     * @param EventDispatcherInterface $eventDispatcher          event dispatcher
     * @param ApiDefinitionLoader      $apiLoader                loader for third party api definition
     * @param array                    $additionalRoutes         custom routes
     * @param array                    $pathWhitelist            serviec path that always get aded to the main page
     * @param array                    $proxySourceConfiguration Set of sources to be recognized by the controller
     */
    public function __construct(
        Router $router,
        Response $response,
        RestUtilsInterface $restUtils,
        EventDispatcherInterface $eventDispatcher,
        ApiDefinitionLoader $apiLoader,
        $additionalRoutes = [],
        $pathWhitelist = [],
        array $proxySourceConfiguration = []
    ) {
        $this->router = $router;
        $this->response = $response;
        $this->restUtils = $restUtils;
        $this->eventDispatcher = $eventDispatcher;
        $this->apiLoader = $apiLoader;
        $this->addditionalRoutes = $additionalRoutes;
        $this->pathWhitelist = $pathWhitelist;
        $this->proxySourceConfiguration = $proxySourceConfiguration;
    }

    /**
     * create simple start page.
     *
     * @return Response $response Response with result or error
     */
    public function indexAction()
    {
        $response = $this->response;

        $mainPage = new \stdClass();
        $mainPage->services = $this->determineServices(
            $this->restUtils->getOptionRoutes()
        );

        $mainPage->thirdparty = $this->registerThirdPartyServices();

        $response->setContent(json_encode($mainPage));
        $response->setStatusCode(Response::HTTP_OK);

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Determines what service endpoints are available.
     *
     * @param array $optionRoutes List of routing options.
     *
     * @return array
     */
    protected function determineServices(array $optionRoutes)
    {
        $router = $this->router;
        foreach ($this->addditionalRoutes as $route) {
            $optionRoutes[$route] = null;
        }

        $services = array_map(
            function ($routeName) use ($router) {
                $routeParts = explode('.', $routeName);
                if (count($routeParts) > 3) {
                    list($app, $bundle, $rest, $document) = $routeParts;

                    $schemaRoute = implode('.', [$app, $bundle, $rest, $document, 'canonicalSchema']);

                    return [
                        '$ref' => $router->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'profile' => $router->generate($schemaRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ];
                }
            },
            array_keys($optionRoutes)
        );

        $sortArr = [];
        foreach ($services as $key => $val) {
            if ($this->isRelevantForMainPage($val) && !in_array($val['$ref'], $sortArr)) {
                $sortArr[$key] = $val['$ref'];
            } else {
                unset($services[$key]);
            }
        }

        // get additional routes
        $additionalRoutes = $this->getAdditionalRoutes($sortArr);

        $services = array_merge($services, $additionalRoutes);

        array_multisort($sortArr, SORT_ASC, $services);
        return $services;
    }

    /**
     * gets the additional routes that can be injected by listeners/subscribers
     *
     * @param array $sortArr array needed for sorting
     *
     * @return array additional routes
     */
    private function getAdditionalRoutes(array &$sortArr)
    {
        $additionalRoutes = [];
        $event = new HomepageRenderEvent();
        $routes = $this->eventDispatcher->dispatch($event, HomepageRenderEvent::EVENT_NAME)->getRoutes();

        if (!empty($routes)) {
            $baseRoute = $this->router->match("/");
            $baseUrl = $this->router->generate($baseRoute['_route'], [], UrlGeneratorInterface::ABSOLUTE_URL);
            foreach ($routes as $route) {
                $thisUrl = $baseUrl.$route['$ref'];
                $additionalRoutes[] = [
                    '$ref' => $thisUrl,
                    'profile' => $baseUrl.$route['profile']
                ];
                $sortArr[$thisUrl] = $thisUrl;
            }
        }

        return $additionalRoutes;
    }

    /**
     * tells if a service is relevant for the mainpage
     *
     * @param array $val value of service spec
     *
     * @return boolean
     */
    private function isRelevantForMainPage($val)
    {
        return (substr($val['$ref'], -1) === '/')
            || in_array(parse_url($val['$ref'], PHP_URL_PATH), $this->pathWhitelist);
    }

    /**
     * Resolves all third party routes and add schema info
     *
     * @param array $thirdApiRoutes list of all routes from an API
     *
     * @return array
     */
    protected function determineThirdPartyServices(array $thirdApiRoutes)
    {
        $definition = $this->apiLoader;
        $mainRoute = $this->router->generate(
            'graviton.core.static.main.all',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $services = array_map(
            function ($apiRoute) use ($mainRoute, $definition) {

                return array (
                    '$ref' => $mainRoute.$apiRoute,
                    'profile' => $mainRoute."schema/".$apiRoute."/item",
                );
            },
            $thirdApiRoutes
        );

        return $services;
    }

    /**
     * Finds configured external apis to be exposed via G2.
     *
     * @return array
     */
    private function registerThirdPartyServices()
    {
        $services = [];

        foreach (array_keys($this->proxySourceConfiguration) as $source) {
            foreach ($this->proxySourceConfiguration[$source] as $thirdparty => $option) {
                $this->apiLoader->resetDefinitionLoader();
                $this->apiLoader->setOption($option);
                $this->apiLoader->addOptions($this->decideApiAndEndpoint($option));
                $services[$thirdparty] = $this->determineThirdPartyServices(
                    $this->apiLoader->getAllEndpoints(false, true)
                );
            }
        }

        return $services;
    }

    /**
     * get API name and endpoint from the url (third party API)
     *
     * @param array $config Configuration information ['prefix', 'serviceEndpoint']
     *
     * @return array
     */
    protected function decideApiAndEndpoint(array $config)
    {
        if (array_key_exists('serviceEndpoint', $config)) {
            return array (
                "apiName" => $config['prefix'],
                "endpoint" => $config['serviceEndpoint'],
            );
        }

        return [];
    }

    /**
     * Return OPTIONS results.
     *
     * @param Request $request Current http request
     *
     * @return Response $response Result of the action
     */
    public function optionsAction(Request $request)
    {
        $response = $this->response;
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
        $request->attributes->set('corsMethods', 'GET, OPTIONS');

        return $response;
    }
}
