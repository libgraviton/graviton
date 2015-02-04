<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Load routes for all rest services
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class BasicLoader extends Loader implements ContainerAwareInterface
{
    /**
     * @var boolean
     */
    private $loaded = false;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $routes;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Routing\RouteCollection $routes route collection
     *
     * @return BasicLoader
     */
    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    /**
     * Load routes for all services tagged with graviton.rest
     *
     * @param string $resource unused
     * @param string $type     unused
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "graviton.rest.routing.loader" loader twice');
        }

        $container = $this->getContainerBuilder();
        foreach ($container->findTaggedServiceIds('graviton.rest') as $service => $serviceConfig) {
            $this->loadService($service, $serviceConfig);
        }

        $this->loaded = true;

        return $this->routes;
    }

    /**
     * Loads the ContainerBuilder from the cache.
     *
     * @return ContainerBuilder
     *
     * @throws \LogicException
     */
    protected function getContainerBuilder()
    {
        if (!is_file(
            $cachedFile = $this->getContainer()
                               ->getParameter('debug.container.dump')
        )
        ) {
            throw new \LogicException('Debug information about the container could not be found.');
        }

        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($cachedFile);

        return $container;
    }

    /**
     * get the container
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * set container
     *
     * @param ContainerInterface $container global container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * load routes for a single service
     *
     * @param string $service       service name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    private function loadService($service, $serviceConfig)
    {
        list($app, $bundle, , $entity) = explode('.', $service);
        $resource = implode('.', array($app, $bundle, 'rest', $entity));

        $this->loadReadOnlyRoutes($service, $resource, $serviceConfig);
        if (!($serviceConfig[0] && array_key_exists('read-only', $serviceConfig[0]))) {
            $this->loadWriteRoutes($service, $resource, $serviceConfig);
        }
    }

    /**
     * generate ro routes
     *
     * @param string $service       service name
     * @param string $resource      resource name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    public function loadReadOnlyRoutes($service, $resource, $serviceConfig)
    {
        $actionGet = ActionUtils::getRouteGet($service, $serviceConfig);
        $this->routes->add($resource . '.get', $actionGet);

        $actionAll = ActionUtils::getRouteAll($service, $serviceConfig);
        $this->routes->add($resource . '.all', $actionAll);

        $actionOptions = ActionUtils::getRouteOptions($service, $serviceConfig);
        $this->routes->add($resource . '.options', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'collection');
        $this->routes->add($resource . '.canonicalSchema', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig);
        $this->routes->add($resource . '.canonicalIdSchema', $actionOptions);

        $actionOptions = ActionUtils::getRouteOptions($service, $serviceConfig, array('id' => '\w+'));
        $this->routes->add($resource . '.idOptions', $actionOptions);
    }

    /**
     * generate write routes
     *
     * @param string $service       service name
     * @param string $resource      resource name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    public function loadWriteRoutes($service, $resource, $serviceConfig)
    {
        $actionPost = ActionUtils::getRoutePost($service, $serviceConfig);
        $this->routes->add($resource . '.post', $actionPost);

        $actionPut = ActionUtils::getRoutePut($service, $serviceConfig);
        $this->routes->add($resource . '.put', $actionPut);

        $actionPatch = ActionUtils::getRoutePatch($service, $serviceConfig);
        $this->routes->add($resource . '.patch', $actionPatch);

        $actionDelete = ActionUtils::getRouteDelete($service, $serviceConfig);
        $this->routes->add($resource . '.delete', $actionDelete);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $resource unused
     * @param string $type     Type to match against
     *
     * @return Boolean
     */
    public function supports($resource, $type = null)
    {
        return 'graviton.rest.routing.loader' === $type;
    }
}
