<?php
/**
 * ParamConverter class for entry point to Analytics Bundle
 */

namespace Graviton\AnalyticsBundle\Request\ParamConverter;

use Graviton\AnalyticsBundle\Helper\JsonMapper;
use Graviton\AnalyticsBundle\Manager\AnalyticsManager;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Service Request Converter and startup for Analytics
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ServiceConverter implements ParamConverterInterface
{
    /** Cache name for services */
    const CACHE_KEY_SERVICES = 'analytics_services';
    const CACHE_KEY_SERVICES_TIME = 10;
    const CACHE_KEY_SERVICES_URLS = 'analytics_services_urls';
    const CACHE_KEY_SERVICES_URLS_TIME = 10;

    /** @var AnalyticsManager */
    protected $analyticsManager;

    /** @var CacheProvider */
    protected $cacheProvider;

    /** @var Router */
    protected $router;

    /** @var string */
    protected $directory;

    /** @var array  */
    private $services = [];

    /**
     * ServiceConverter constructor.
     * @param AnalyticsManager $analyticsManager    Db Manager and query control
     * @param CacheProvider    $cacheProvider       Cache service
     * @param Router           $router              To manage routing generation
     * @param string           $definitionDirectory Where definitions are stored
     */
    public function __construct(
        AnalyticsManager $analyticsManager,
        CacheProvider $cacheProvider,
        Router $router,
        $definitionDirectory
    ) {
        $this->analyticsManager = $analyticsManager;
        $this->cacheProvider = $cacheProvider;
        $this->router = $router;
        $this->directory = $definitionDirectory;
        $this->init();
    }

    /**
     * Scan base root directory for analytic definitions
     * @return void
     */
    private function init()
    {
        $this->services = $this->cacheProvider->fetch(self::CACHE_KEY_SERVICES);
        if (is_array($this->services)) {
            return;
        }
        $this->services = [];
        if (strpos($this->directory, 'vendor/graviton/graviton')) {
            $this->directory = str_replace('vendor/graviton/graviton/', '', $this->directory);
        }
        $finder = new Finder();
        $finder->files()->in($this->directory)
            ->name('*.json')
            ->notName('_*');
        foreach ($finder as $file) {
            $key = $file->getFilename();
            $data = json_decode($file->getContents());
            if (json_last_error()) {
                throw new InvalidConfigurationException(
                    sprintf('Analytics file: %s could not be loaded due to error: ', $key, json_last_error_msg())
                );
            }
            $this->services[$data->route] = $data;
        }
        $this->cacheProvider->save(self::CACHE_KEY_SERVICES, $this->services, self::CACHE_KEY_SERVICES_TIME);
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
            return ['services' => $services];
        }
        $services = [];
        $r = $this->router;
        $b = $r->getContext()->getScheme().'://'.$r->getContext()->getHost().':'.$r->getContext()->getHttpPort();
        foreach ($this->services as $name => $service) {
            $services[] = [
                '$ref' => $b.$r->generate('graviton_analytics_service', ['service' => $service->route], false),
                'profile' => $b.$r->generate('graviton_analytics_service_schema', ['service' => $service->route], true)
            ];
        }
        $this->cacheProvider->save(self::CACHE_KEY_SERVICES_URLS, $services, self::CACHE_KEY_SERVICES_URLS_TIME);
        return ['services' => $services];
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
        // Locate the schema definition
        if (!array_key_exists($name, $this->services)) {
            throw new NotFoundHttpException(
                sprintf('Service Analytics for %s was not found', $name)
            );
        }
        $mapper = new JsonMapper();
        /** @var AnalyticModel $schema */
        $schema = $mapper->map($this->services[$name], new AnalyticModel());
        return $schema;
    }

    /**
     * Will map and find data for defined route
     *
     * @param string $serviceRoute Route name for service
     * @return array
     */
    public function getData($serviceRoute)
    {
        // Locate the schema definition
        $schema = $this->getServiceSchemaByRoute($serviceRoute);

        return $this->analyticsManager->getData($schema);
    }

    /**
     * Schema definition
     *
     * @param string $serviceRoute Route name for service
     * @return mixed
     */
    public function getSchema($serviceRoute)
    {
        // Locate the schema definition
        $schema =  $this->getServiceSchemaByRoute($serviceRoute);

        return $schema->getSchema();
    }

    /**
     * Which service can load this Converter
     *
     * @param ParamConverter $configuration Configuration data
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @param Request        $request       SF request bag
     * @param ParamConverter $configuration Config data
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     * @return void
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        // we could use the request here if needed. $this->setRequest($request);
        $manager = $this;

        // Map found Service to the route's parameter
        $request->attributes->set($configuration->getName(), $manager);
    }
}
