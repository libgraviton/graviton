<?php
/**
 * controller for start page
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\CoreBundle\Event\HomepageRenderEvent;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\RestBundle\Trait\SchemaTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * MainController
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class MainController
{

    use SchemaTrait;

    /**
     * @param Router                   $router          router
     * @param RestUtils                $restUtils       rest-utils from GravitonRestBundle
     * @param EventDispatcherInterface $eventDispatcher event dispatcher
     */
    public function __construct(
        private Router $router,
        private RestUtils $restUtils,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * create simple start page.
     *
     * @param Request $request request
     *
     * @return Response $response Response with result or error
     */
    public function indexAction(Request $request)
    {
        $mainPage = [];

        $baseUri = $this->router->generate(
            $request->attributes->get('_route'),
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $mainPage['services'] = array_merge(
            [
                [
                    '$ref' => $baseUri,
                    'api-docs' => [
                        'json' => ['$ref' => $baseUri.'openapi.json'],
                        'yaml' => ['$ref' => $baseUri.'openapi.yaml']
                    ]
                ]
            ],
            $this->determineServices()
        );

        return new JsonResponse($mainPage);
    }

    /**
     * Determines what service endpoints are available.
     *
     * @return array
     */
    private function determineServices()
    {
        $routes = [];
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $isRest = $route->getDefault('graviton-rest');
            if (!$isRest) {
                continue;
            }

            $routerBase = $route->getDefault('router-base');
            if (empty($routerBase) || !str_contains($route->getPath(), '/schema/')) {
                continue;
            }

            $routes[$routerBase][] = $routeName;
        }

        $services = [];
        foreach ($routes as $routerBase => $subRoutes) {
            $match = $this->router->match($routerBase);
            $baseRoute = $match['_route'];

            $schemas = array_map(
                function ($routeName) {
                    return $this->router->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL);
                },
                $subRoutes
            );

            natsort($schemas);

            $docs = [];
            foreach ($schemas as $schema) {
                if (str_ends_with($schema, '.json')) {
                    $type = 'json';
                } else {
                    $type = 'yaml';
                }

                $docs[$type] = ['$ref' => $schema];
            }

            $services[] = [
                '$ref' => $this->router->generate($baseRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'api-docs' => $docs
            ];
        }

        $sortArr = [];
        foreach ($services as $key => $val) {
            if (!in_array($val['$ref'], $sortArr)) {
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
                $route['$ref'] = $baseUrl.$route['$ref'];
                $route['api-docs']['json']['$ref'] = $baseUrl.$route['api-docs']['json']['$ref'];
                $route['api-docs']['yaml']['$ref'] = $baseUrl.$route['api-docs']['yaml']['$ref'];

                $sortArr[$route['$ref']] = $route['$ref'];

                $additionalRoutes[] = $route;
            }
        }

        return $additionalRoutes;
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
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Returns the main schema
     *
     * @param Request $request Current http request
     *
     * @return Response $response Result of the action
     */
    public function schemaAction(Request $request)
    {
        if (!class_exists('\GravitonDyn\EntityBundle\Entity\GravitonSchema')) {
            throw new \RuntimeException('Unable to locate global schema.');
        }

        return $this->getResponseFromSchemaFile(
            \GravitonDyn\EntityBundle\Entity\GravitonSchema::MAIN_SCHEMA_FILE,
            $request->get('format')
        );
    }

    /**
     * renders a favicon
     *
     * @return Response $response icon response
     */
    public function iconAction()
    {
        return new BinaryFileResponse(__DIR__.'/../Resources/assets/favicon.ico');
    }
}
