<?php
/**
 * provides accessors to the analytics services
 */

namespace Graviton\AnalyticsBundle\Manager;

use Graviton\AnalyticsBundle\Exception\AnalyticUsageException;
use Graviton\AnalyticsBundle\Helper\JsonMapper;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Graviton\DocumentBundle\Service\DateConverter;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;

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
    const CACHE_KEY_SERVICES_URLS = 'analytics_services_urls';
    const CACHE_KEY_SERVICES_PREFIX = 'analytics_';

    /** @var RequestStack */
    protected $requestStack;

    /** @var AnalyticsManager */
    protected $analyticsManager;

    /** @var AdapterInterface */
    protected $cacheProvider;

    /** @var DateConverter */
    protected $dateConverter;

    /** @var Router */
    protected $router;

    /** @var string */
    protected $directory;

    /** @var int */
    protected $cacheTimeMetadata;

    /** @var Filesystem */
    protected $fs;

    /** @var JsonMapper */
    private $jsonMapper;

    /**
     * @var string
     */
    private $skipCacheHeaderName = 'x-analytics-no-cache';

    /**
     * @var array
     */
    private $analyticsServices = [];

    /**
     * ServiceConverter constructor.
     *
     * @param RequestStack     $requestStack      Sf Request information service
     * @param AnalyticsManager $analyticsManager  Db Manager and query control
     * @param AdapterInterface $cacheProvider     Cache service
     * @param DateConverter    $dateConverter     date converter
     * @param Router           $router            To manage routing generation
     * @param int              $cacheTimeMetadata How long to cache metadata
     * @param array            $analyticsServices the services
     */
    public function __construct(
        RequestStack $requestStack,
        AnalyticsManager $analyticsManager,
        AdapterInterface $cacheProvider,
        DateConverter $dateConverter,
        Router $router,
        $cacheTimeMetadata,
        $analyticsServices
    ) {
        $this->requestStack = $requestStack;
        $this->analyticsManager = $analyticsManager;
        $this->cacheProvider = $cacheProvider;
        $this->dateConverter = $dateConverter;
        $this->router = $router;
        $this->cacheTimeMetadata = $cacheTimeMetadata;
        $this->fs = new Filesystem();
        $this->analyticsServices = $analyticsServices;
        $this->jsonMapper = new JsonMapper();
    }

    /**
     * Return array of available services
     *
     * @return array
     */
    public function getServices()
    {
        $cacheItem = $this->cacheProvider->getItem(self::CACHE_KEY_SERVICES_URLS);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $services = [];
        foreach ($this->analyticsServices as $name => $service) {
            if (is_numeric($name)) {
                continue;
            }

            $services[] = [
                '$ref' => $this->router->generate(
                    'graviton_analytics_service',
                    [
                        'service' => $service['route']
                    ],
                    false
                ),
                'profile' => $this->router->generate(
                    'graviton_analytics_service_schema',
                    [
                        'service' => $service['route']
                    ],
                    true
                )
            ];
        }

        $cacheItem->set($services);
        $cacheItem->expiresAfter($this->cacheTimeMetadata);
        $this->cacheProvider->save($cacheItem);

        return $services;
    }

    /**
     * Get service definition
     *
     * @param string $name Route name for service
     *
     * @throws NotFoundHttpException
     * @return AnalyticModel
     */
    private function getAnalyticModel($name)
    {
        if (!isset($this->analyticsServices[$name])) {
            throw new NotFoundHttpException(
                sprintf('Analytic definition "%s" was not found', $name)
            );
        }

        return $this->jsonMapper->map($this->analyticsServices[$name], new AnalyticModel());
    }

    /**
     * Get the analytic model for current request
     *
     * @return AnalyticModel analytic model
     */
    public function getCurrentAnalyticModel()
    {
        $serviceRoute = $this->requestStack->getCurrentRequest()->get('service');

        // Locate the model definition
        return $this->getAnalyticModel($serviceRoute);
    }

    /**
     * Gets all collections involved in this analytics
     *
     * @return string[] name of collections
     */
    public function getMongoCollections()
    {
        return $this->getCurrentAnalyticModel()->getAllCollections();
    }

    /**
     * Will map and find data for defined route
     *
     * @return array
     */
    public function getData()
    {
        $model = $this->getCurrentAnalyticModel();

        $cacheTime = $model->getCacheTime();
        $cacheItem = $this->cacheProvider->getItem($this->getCacheKey($model));

        //Cached data if configured
        if ($cacheTime &&
            !$this->requestStack->getCurrentRequest()->headers->has($this->skipCacheHeaderName) &&
            $cacheItem->isHit()
        ) {
            return $cacheItem->get();
        }

        $data = $this->analyticsManager->getData($model, $this->getServiceParameters($model));

        if ($cacheTime) {
            $cacheItem->set($data);
            $cacheItem->expiresAfter($cacheTime);
            $this->cacheProvider->save($cacheItem);
        }

        return $data;
    }

    /**
     * generate a cache key also based on query
     *
     * @param AnalyticModel $schema schema
     *
     * @return string cache key
     */
    private function getCacheKey($schema)
    {
        return self::CACHE_KEY_SERVICES_PREFIX
            . $schema->getRoute()
            . sha1(serialize($this->requestStack->getCurrentRequest()->query->all()));
    }

    /**
     * Locate and display service definition schema
     *
     * @return mixed
     */
    public function getSchema()
    {
        $serviceRoute = $this->requestStack->getCurrentRequest()
                                           ->get('service');

        // Locate the schema definition
        $model = $this->getAnalyticModel($serviceRoute);

        return $model->getSchema();
    }

    /**
     * returns the params as passed from the user
     *
     * @param AnalyticModel $model model
     *
     * @return array the params, converted as specified
     * @throws AnalyticUsageException
     */
    private function getServiceParameters(AnalyticModel $model)
    {
        $params = [];
        if (!is_array($model->getParams())) {
            return $params;
        }

        foreach ($model->getParams() as $param) {
            if (!isset($param['name'])) {
                throw new \LogicException("Incorrect spec (no name) of param in analytics route " . $model->getRoute());
            }

            $paramValue = $this->requestStack->getCurrentRequest()->query->get($param['name'], null);

            // default set?
            if (is_null($paramValue) && isset($param['default'])) {
                $paramValue = $param['default'];
            }

            // required missing?
            if (is_null($paramValue) && (isset($param['required']) && $param['required'] === true)) {
                throw new AnalyticUsageException(
                    sprintf(
                        "Missing parameter '%s' in analytics route '%s'",
                        $param['name'],
                        $model->getRoute()
                    )
                );
            }

            if (!is_null($param['type']) && !is_null($paramValue)) {
                switch ($param['type']) {
                    case "integer":
                        $paramValue = intval($paramValue);

                        // more than max? limit to max..
                        if (isset($param['max']) && is_numeric($param['max']) && intval($param['max']) < $paramValue) {
                            $paramValue = intval($param['max']);
                        }
                        break;
                    case "boolean":
                        if ($paramValue === 'true') {
                            $paramValue = true;
                        } elseif ($paramValue === 'false') {
                            $paramValue = false;
                        } else {
                            $paramValue = boolval($paramValue);
                        }
                        break;
                    case "array":
                        $paramValue = explode(',', $paramValue);
                        break;
                    case "date":
                        $paramValue = new UTCDateTime($this->dateConverter->getDateTimeFromString($paramValue));
                        break;
                    case "regex":
                        $paramValue = new Regex($paramValue, 'i');
                        break;
                    case "regexstring":
                        $paramValue = '^'.str_replace('*', '(.*)', $paramValue);
                        break;
                    case "mongoid":
                        $paramValue = new ObjectId($paramValue);
                        break;
                    case "array<integer>":
                        $paramValue = array_map('intval', explode(',', $paramValue));
                        break;
                    case "array<boolean>":
                        $paramValue = array_map('boolval', explode(',', $paramValue));
                        break;
                }
            }

            if (!is_null($paramValue)) {
                $params[$param['name']] = $paramValue;
            }
        }

        return $params;
    }
}
