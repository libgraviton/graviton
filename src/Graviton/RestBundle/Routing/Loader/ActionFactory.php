<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Routing\Route;

class ActionFactory
{
	public static function getRouteGet($service)
	{
		$pattern = '/{id}';
		$defaults = array(
				'_controller' => $service.':getAction',
				'_format' => '~'
		);
		
		$requirements = array(
				'id' => '\d+',
				'_method' => 'GET'
		);
		
		$route = new Route($pattern, $defaults, $requirements);
		
		return $route;
	}
	
	public static function getRouteAll($service)
	{
		$pattern = '/';
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
		$pattern = '/';
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
		$pattern = '/{id}';
		$defaults = array(
				'_controller' => $service.':putAction',
				'_format' => '~'
		);
		
		$requirements = array(
				'id' => '\d+',
				'_method' => 'PUT'
		);
		
		$route = new Route($pattern, $defaults, $requirements);
		
		return $route;
	}
	
	public static function getRouteDelete($service)
	{
		$pattern = '/{id}';
		$defaults = array(
				'_controller' => $service.':deleteAction',
				'_format' => '~'
		);
		
		$requirements = array(
				'id' => '\d+',
				'_method' => 'DELETE'
		);
		
		$route = new Route($pattern, $defaults, $requirements);
		
		return $route;
	}
}