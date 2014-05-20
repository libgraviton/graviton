<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Routing\Route;

class ActionFactory
{
    public static function getRouteGet($service)
    {
        $pattern = '/'.static::getEntityFromService($service).'/{id}';
        $defaults = array(
                '_controller' => $service.':getAction',
                '_format' => '~',
        );
        
        $requirements = array(
                'id' => '\w+',
                '_method' => 'GET',
        );
        
        $route = new Route($pattern, $defaults, $requirements);
        
        return $route;
    }
    
    public static function getRouteAll($service)
    {
        $pattern = '/'.static::getEntityFromService($service);
        $defaults = array(
                '_controller' => $service.':allAction',
                '_format' => '~'
        );
        
        $requirements = array(
                '_method' => 'GET'
        );
        
        $route = new Route($pattern, $defaults, $requirements);
        
        return $route;
    }
    
    public static function getRoutePost($service)
    {
        $pattern = '/'.static::getEntityFromService($service);
        $defaults = array(
                '_controller' => $service.':postAction',
                '_format' => '~'
        );
        
        $requirements = array(
                '_method' => 'POST'
        );
        
        $route = new Route($pattern, $defaults, $requirements);
        
        return $route;
    }
    
    public static function getRoutePut($service)
    {
        $pattern = '/'.static::getEntityFromService($service).'/{id}';
        $defaults = array(
                '_controller' => $service.':putAction',
                '_format' => '~'
        );
        
        $requirements = array(
                'id' => '\w+',
                '_method' => 'PUT'
        );
        
        $route = new Route($pattern, $defaults, $requirements);
        
        return $route;
    }
    
    public static function getRouteDelete($service)
    {
        $pattern = '/'.static::getEntityFromService($service).'/{id}';
        $defaults = array(
                '_controller' => $service.':deleteAction',
                '_format' => '~'
        );
        
        $requirements = array(
                'id' => '\w+',
                '_method' => 'DELETE'
        );
        
        $route = new Route($pattern, $defaults, $requirements);
        
        return $route;
    }

    private static function getEntityFromService($service)
    {
        $parts = explode('.', $service);
        return array_pop($parts);
    }
}
