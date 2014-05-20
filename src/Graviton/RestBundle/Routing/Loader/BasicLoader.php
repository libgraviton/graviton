<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Graviton\RestBundle\Routing\RouteFactory;
use Graviton\RestBundle\Routing\Loader\ActionFactory;
use Graviton\RestBundle\ControllerCollection;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class BasicLoader extends Loader implements ContainerAwareInterface
{
    private $loaded = false;

    private $container;

    /**
     * set container
     *
     * @param ContainerInterface $container global container
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
    public function getContainer() {
        return $this->container;
    }
    
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "graviton.rest.routing.loader" loader twice');
        }

        $routes = new RouteCollection();

        $container = $this->getContainerBuilder();
        foreach (array_keys($container->findTaggedServiceIds('graviton.rest')) AS $service) {
            list($app, $bundle, $type, $entity) = explode('.', $service);
            $resource = implode('.', array($app, $bundle, 'rest', $entity));

            $actionGet = ActionFactory::getRouteGet($service);
            $routes->add($resource.'.get', $actionGet);
        
            $actionAll = ActionFactory::getRouteAll($service);
            $routes->add($resource.'.all', $actionAll);
            
            if (!$this->readOnly) {
                $actionPost = ActionFactory::getRoutePost($service);
                $routes->add($resource.'.post', $actionPost);
            
                $actionPut = ActionFactory::getRoutePut($service);
                $routes->add($resource.'.put', $actionPut);
            
                $actionDelete = ActionFactory::getRouteDelete($service);
                $routes->add($resource.'.delete', $actionDelete);
            }
        }

        $this->loaded = true;
    
        return $routes;
    }
    
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
            throw new \LogicException(sprintf('Debug information about the container could not be found. Please clear the cache and try again.'));
        }

        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($cachedFile);

        return $container;
    }

}
