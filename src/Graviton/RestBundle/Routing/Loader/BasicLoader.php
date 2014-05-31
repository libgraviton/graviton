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

        $routes = new RouteCollection();

        $container = $this->getContainerBuilder();
        foreach ($container->findTaggedServiceIds('graviton.rest') as $service => $serviceConfig) {
            list($app, $bundle, $type, $entity) = explode('.', $service);
            $resource = implode('.', array($app, $bundle, 'rest', $entity));

            $actionGet = ActionFactory::getRouteGet($service);
            $routes->add($resource.'.get', $actionGet);

            $actionAll = ActionFactory::getRouteAll($service);
            $routes->add($resource.'.all', $actionAll);

            if ($serviceConfig[0] && array_key_exists('read-only', $serviceConfig[0])) {
                continue;
            }

            $actionPost = ActionFactory::getRoutePost($service);
            $routes->add($resource.'.post', $actionPost);

            $actionPut = ActionFactory::getRoutePut($service);
            $routes->add($resource.'.put', $actionPut);

            $actionDelete = ActionFactory::getRouteDelete($service);
            $routes->add($resource.'.delete', $actionDelete);
        }

        $this->loaded = true;

        return $routes;
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
}
