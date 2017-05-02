<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 04.04.17
 * Time: 09:50
 */

namespace Graviton\ApiBundle\Service;


use Graviton\ApiBundle\Manager\DatabaseManager;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\JsonSchemaBundle\Validator\InvalidJsonException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiService
{
    /** @var string Where services are located */
    protected $dirService;

    /** @var string Where we will cache definitions trees */
    protected $dirCache;

    /** @var DatabaseManager */
    protected $dbManager;

    /** @var SchemaService Schema Definition */
    private $schemaService;

    /** @var ConfigService Config Definition */
    private $configService;

    /** @var MappingService Config Definition */
    private $mappingService;

    /** @var RequestStack */
    protected $requestStack;

    /** @var string Host and Schema */
    private $baseUri;

    /** @var string Service Definition */
    private $requestedService;

    /** @var string Requested class id */
    private $classId;

    /** @var string Requested collection id */
    private $collectionId;


    public function __construct(
        RequestStack $requestStack,
        ConfigService $configService,
        SchemaService $schemaService,
        MappingService $mappingService,
        DatabaseManager $dbManager
    )
    {
        $this->requestStack = $requestStack;
        $this->schemaService = $schemaService;
        $this->configService = $configService;
        $this->mappingService = $mappingService;
        $this->dbManager = $dbManager;
        $this->init();
    }

    /**
     * Staring the needed params.
     */
    private function init()
    {
        $request = $this->requestStack->getMasterRequest();
        $this->baseUri = $request->getSchemeAndHttpHost() . $request->getPathInfo();
        $this->requestedService = $request->get('service');

        $this->extractServiceRequest();
    }

    public function getRoutes()
    {
        $routes = $this->configService->getJsonFromFile('routes.json');

        // Remove trailing slash
        $baseUri = rtrim($this->baseUri, '/');
        $services = [];

        foreach ($routes as $route) {
            $services[] = [
                '$ref' => $baseUri . '/' . $route,
                'profile' => $baseUri . '/schema/' . $route . '/collection'
            ];
        }


        return ['services' => $services];
    }

    public function getSchema()
    {
        return $this->schemaService->getSchema($this->classId);
    }

    public function getData()
    {
        $schema = $this->getSchema();
        if ($this->collectionId) {
            $data = $this->dbManager->findOne($schema->{'x-documentClass'}, $this->collectionId);
            if (!$data) {
                throw new NotFoundException('Entry with id not found!');
            }
        } else {
            $data = $this->dbManager->findAll($schema->{'x-documentClass'});
        }
        return $this->mappingService->mapData($data, $schema);
    }

    /**
     * Will extract and set variables for
     *
     * @param string $requestService Requested service call
     */
    private function extractServiceRequest()
    {
        if (!$this->requestedService) {
            return;
        }
        $requestService = trim($this->requestedService, "/");
        $routes = $this->configService->getJsonFromFile('routes.json');
        $matchedRoute = $this->findMatchedService($requestService, $routes);

        $serviceRoute = reset($matchedRoute);
        $serviceId = (array_keys($matchedRoute));

        if ($id = substr($requestService, strlen($serviceRoute))) {
            $this->collectionId = trim($id, '/');
        }

        $this->classId = $serviceId[0];
    }

    /**
     * Will return the must accurate requested Service match.
     * request service may be:
     * foo
     * foo/bar
     * foo/bar/one
     * for/bar/two
     * for/{id}
     * for/bar/{id}
     * for/bar/one/{id}
     *
     * @param $requestService
     * @param $routes
     * @return array
     */
    private function findMatchedService($requestService, $routes)
    {
        $matched = [];
        $routes = (array) $routes;
        $match = array_search($requestService, $routes);

        // Fast matching return
        if ($match && strlen($routes[$match]) === strlen($requestService)) {
            $matched[$match] = $requestService;
            return $matched;
        }

        // When id is given in request
        foreach ($routes as $serviceId => $route) {
            if ($this->startsWith($route, $requestService)) {
                $matched[$serviceId] = $route;
            } elseif ($this->startsWith($requestService, $route)) {
                $matched[$serviceId] = $route;
            }
        }

        // Some routes have same start, but we do not control definition load
        if (count($matched) > 1) {
            $score = [];
            foreach ($matched as $serviceId => $route) {
                if (strpos($requestService, $route) !== false) {
                    $sbResult = substr($requestService, strlen($route));
                    $score[strlen($sbResult)] = $serviceId;
                }
            }
            if (!empty($score)) {
                ksort($score);
                $score = reset($score);
                $matched = [$score => $matched[$score]];
            } else {
                throw new NotFoundException(
                    sprintf('Sorry, no route matched. Did you mean: %s', implode(', ', $matched))
                );
            }
        }

        // Can happen we match a route that's incomplete
        $matchedUrl = reset($matched);
        if (strlen($matchedUrl) > strlen($requestService)) {
            throw new NotFoundException(
                sprintf('Sorry, no route matched. Did you mean: %s', $matchedUrl)
            );
        }

        if (empty($matched)) {
            throw new NotFoundException('Service not found with given url');
        }
        return $matched;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

}