<?php
/**
 * ParamConverter class for entry point to Analytics Bundle
 */

namespace Graviton\AnalyticsBundle\Manager;

use Graviton\AnalyticsBundle\Helper\JsonMapper;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Service Request Converter and startup for Analytics
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ServiceManager
{
    /** Cache name for services */
    const CACHE_KEY_SERVICES = 'analytics_services';
    const CACHE_KEY_SERVICES_TIME = 10;
    const CACHE_KEY_SERVICES_URLS = 'analytics_services_urls';
    const CACHE_KEY_SERVICES_URLS_TIME = 10;
    const CACHE_KEY_SERVICES_PREFIX = 'analytics_';

    /** @var RequestStack */
    protected $requestStack;

    /** @var AnalyticsManager */
    protected $analyticsManager;

    /** @var CacheProvider */
    protected $cacheProvider;

    /** @var Router */
    protected $router;

    /** @var string */
    protected $directory;

    /**
     * ServiceConverter constructor.
     * @param RequestStack     $requestStack        Sf Request information service
     * @param AnalyticsManager $analyticsManager    Db Manager and query control
     * @param CacheProvider    $cacheProvider       Cache service
     * @param Router           $router              To manage routing generation
     * @param string           $definitionDirectory Where definitions are stored
     */
    public function __construct(
        RequestStack $requestStack,
        AnalyticsManager $analyticsManager,
        CacheProvider $cacheProvider,
        Router $router,
        $definitionDirectory
    ) {
        $this->requestStack = $requestStack;
        $this->analyticsManager = $analyticsManager;
        $this->cacheProvider = $cacheProvider;
        $this->router = $router;
        $this->directory = $definitionDirectory;
    }

    /**
     * Scan base root directory for analytic definitions
     * @return array
     */
    private function getDirectoryServices()
    {
        $services = $this->cacheProvider->fetch(self::CACHE_KEY_SERVICES);

        if (is_array($services)) {
            return $services;
        }

        $services = [];
        if (strpos($this->directory, 'vendor/graviton/graviton')) {
            $this->directory = str_replace('vendor/graviton/graviton/', '', $this->directory);
        }
        if (!is_dir($this->directory)) {
            return $services;
        }

        $finder = new Finder();
        $finder
            ->files()
            ->in($this->directory)
            ->path('/\/analytics\//i')
            ->name('*.json')
            ->notName('_*')
            ->sortByName();

        foreach ($finder as $file) {
            $key = $file->getFilename();
            $data = json_decode($file->getContents());
            if (json_last_error()) {
                throw new InvalidConfigurationException(
                    sprintf('Analytics file: %s could not be loaded due to error: ', $key, json_last_error_msg())
                );
            }
            $services[$data->route] = $data;
        }

        $this->cacheProvider->save(self::CACHE_KEY_SERVICES, $services, self::CACHE_KEY_SERVICES_TIME);
        return $services;
    }

    /**
     * Return array of available services
     *
     * @return array
     */
    public function getServices()
    {
        $services = $this->cacheProvider->fetch(self::CACHE_KEY_SERVICES_URLS);
        if (is_array($services)) {
            return $services;
        }
        $services = [];
        foreach ($this->getDirectoryServices() as $name => $service) {
            $services[] = [
                '$ref' => $this->router->generate(
                    'graviton_analytics_service',
                    [
                        'service' => $service->route
                    ],
                    false
                ),
                'profile' => $this->router->generate(
                    'graviton_analytics_service_schema',
                    [
                        'service' => $service->route
                    ],
                    true
                )
            ];
        }
        $this->cacheProvider->save(
            self::CACHE_KEY_SERVICES_URLS,
            $services,
            self::CACHE_KEY_SERVICES_URLS_TIME
        );
        return $services;
    }

    /**
     * Get service definition
     *
     * @param string $name Route name for service
     * @throws NotFoundHttpException
     * @return AnalyticModel
     */
    private function getServiceSchemaByRoute($name)
    {
        $services = $this->getDirectoryServices();
        // Locate the schema definition
        if (!array_key_exists($name, $services)) {
            throw new NotFoundHttpException(
                sprintf('Service Analytics for %s was not found', $name)
            );
        }

        $mapper = new JsonMapper();
        /** @var AnalyticModel $schema */
        $schema = $mapper->map($services[$name], new AnalyticModel());
        return $schema;
    }

    /**
     * Will map and find data for defined route
     *
     * @return array
     */
    public function getData()
    {
        $serviceRoute = $this->requestStack->getCurrentRequest()->get('service');

        // Locate the schema definition
        $schema = $this->getServiceSchemaByRoute($serviceRoute);
        $cacheTime = $schema->getCacheTime();

        // check parameters

        //Cached data if configured
        if ($cacheTime &&
            $cache = $this->cacheProvider->fetch(self::CACHE_KEY_SERVICES_PREFIX.$schema->getRoute())
        ) {
            return $cache;
        }

        $data = $this->analyticsManager->getData($schema, $this->getServiceParameters($schema));

        if ($cacheTime) {
            $this->cacheProvider->save(self::CACHE_KEY_SERVICES_PREFIX.$schema->getRoute(), $data, $cacheTime);
        }

        return $data;
    }

    /**
     * Locate and display service definition schema
     *
     * @return mixed
     */
    public function getSchema()
    {
        $serviceRoute = $this->requestStack->getCurrentRequest()->get('service');

        // Locate the schema definition
        $schema =  $this->getServiceSchemaByRoute($serviceRoute);

        return $schema->getSchema();
    }

    /**
     * returns the params as passed from the user
     *
     * @param AnalyticModel $model model
     *
     * @return array the params, converted as specified
     */
    private function getServiceParameters(AnalyticModel $model)
    {
        $params = [];
        if (!is_array($model->getParams())) {
            return $params;
        }

        foreach ($model->getParams() as $param) {
            if (!isset($param->name)) {
                throw new \LogicException("Incorrect spec (no name) of param in analytics route " . $model->getRoute());
            }

            $paramValue = $this->requestStack->getCurrentRequest()->query->get($param->name, null);

            // required missing?
            if (is_null($paramValue) && (isset($param->required) && $param->required === true)) {
                throw new \LogicException(
                    sprintf(
                        "Missing parameter '%s' in analytics route '%s'",
                        $param->name,
                        $model->getRoute()
                    )
                );
            }

            if (!is_null($param->type)) {
                switch ($param->type) {
                    case "integer":
                        $paramValue = intval($paramValue);
                        break;
                    case "boolean":
                        $paramValue = boolval($paramValue);
                        break;
                }
            }

            $params[$param->name] = $paramValue;
        }

        return $params;
    }
}
