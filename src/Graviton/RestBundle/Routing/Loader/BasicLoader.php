<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Load routes for all rest services
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class BasicLoader extends Loader implements ContainerAwareInterface
{
    private $loaded = false;

    private $container;

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
     * get the container
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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

    /**
     * Loads the ContainerBuilder from the cache.
     *
     * @return ContainerBuilder
     *
     * @throws \LogicException
     */
    protected function getContainerBuilder()
    {
        if (!is_file($cachedFile = $this->getContainer()->getParameter('debug.container.dump'))) {
            throw new \LogicException('Debug information about the container could not be found.');
        }

        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($cachedFile);

        return $container;
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
        list($app, $bundle, $type, $entity) = explode('.', $service);
        $resource = implode('.', array($app, $bundle, 'rest', $entity));

        $this->loadReadOnlyRoutes($service, $resource);
        if (!($serviceConfig[0] && array_key_exists('read-only', $serviceConfig[0]))) {
            $this->loadWriteRoutes($service, $resource);
        }
    }

    /**
     * generate ro routes
     *
     * @param string $service  service name
     * @param string $resource resource name
     *
     * @return void
     */
    public function loadReadOnlyRoutes($service, $resource)
    {
        $actionGet = ActionUtils::getRouteGet($service);
        $this->routes->add($resource.'.get', $actionGet);

        $actionAll = ActionUtils::getRouteAll($service);
        $this->routes->add($resource.'.all', $actionAll);
    }

    /**
     * generate write routes
     *
     * @param string $service  service name
     * @param string $resource resource name
     *
     * @return void
     */
    public function loadWriteRoutes($service, $resource)
    {
        $actionPost = ActionUtils::getRoutePost($service);
        $this->routes->add($resource.'.post', $actionPost);

        $actionPut = ActionUtils::getRoutePut($service);
        $this->routes->add($resource.'.put', $actionPut);

        $actionDelete = ActionUtils::getRouteDelete($service);
        $this->routes->add($resource.'.delete', $actionDelete);
    }
}
