<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;
use Graviton\RestBundle\Routing\RouteFactory;
use Graviton\RestBundle\Routing\Loader\ActionFactory;

class BasicLoader implements LoaderInterface
{
    private $loaded = false;
    private $readOnly;
    
    public function __construct($readOnly = false)
    {
        $this->readOnly = $readOnly;
    }
    
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }
    
        $routes = new RouteCollection();

        $actionGet = ActionFactory::getRouteGet($resource);
        $routes->add($resource.'_get', $actionGet);
        
        $actionAll = ActionFactory::getRouteAll($resource);
        $routes->add($resource.'_all', $actionAll);
            
        if (!$this->readOnly) {
            $actionPost = ActionFactory::getRoutePost($resource);
            $routes->add($resource.'_post', $actionPost);
            
            $actionPut = ActionFactory::getRoutePut($resource);
            $routes->add($resource.'_put', $actionPut);
            
            $actionDelete = ActionFactory::getRouteDelete($resource);
            $routes->add($resource.'_delete', $actionDelete);
        }

        //$this->loaded = true;
    
        return $routes;
    }
    
    public function supports($resource, $type = null)
    {
        return 'graviton_rest.routing_loader' === $type;
    }
    
    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }
    
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}
