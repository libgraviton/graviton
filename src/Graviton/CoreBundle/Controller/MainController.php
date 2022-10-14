<?php
/**
 * controller for start page
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\CoreBundle\Event\HomepageRenderEvent;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var RestUtilsInterface
     */
    private $restUtils;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $addditionalRoutes;

    /**
     * @var array
     */
    private $pathWhitelist;

    /**
     * @param Router                   $router                   router
     * @param RestUtilsInterface       $restUtils                rest-utils from GravitonRestBundle
     * @param EventDispatcherInterface $eventDispatcher          event dispatcher
     * @param array                    $additionalRoutes         custom routes
     * @param array                    $pathWhitelist            serviec path that always get aded to the main page
     */
    public function __construct(
        Router $router,
        RestUtilsInterface $restUtils,
        EventDispatcherInterface $eventDispatcher,
        array $additionalRoutes = [],
        array $pathWhitelist = []
    ) {
        $this->router = $router;
        $this->restUtils = $restUtils;
        $this->eventDispatcher = $eventDispatcher;
        $this->addditionalRoutes = $additionalRoutes;
        $this->pathWhitelist = $pathWhitelist;
    }

    /**
     * create simple start page.
     *
     * @return Response $response Response with result or error
     */
    public function indexAction()
    {
        $mainPage = [];
        $mainPage['services'] = $this->determineServices(
            $this->restUtils->getOptionRoutes()
        );

        return new JsonResponse($mainPage);
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

        $services = array_filter(
            $services,
            function ($val) {
                return !is_null($val);
            }
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
}
