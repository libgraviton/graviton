<?php
namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Routing\Route;

/**
 * Generate routes for individual actions
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ActionFactory
{
    /**
     * Get route for GET requests
     *
     * @param String $service service id
     *
     * @return Route
     */
    public static function getRouteGet($service)
    {
        $pattern = '/'.static::getBaseFromService($service).'/{id}';
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

    /**
     * Get route for getAll requests
     *
     * @param String $service service id
     *
     * @return Route
     */
    public static function getRouteAll($service)
    {
        $pattern = '/'.static::getBaseFromService($service);
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

    /**
     * Get route for POST requests
     *
     * @param String $service service id
     *
     * @return Route
     */
    public static function getRoutePost($service)
    {
        $pattern = '/'.static::getBaseFromService($service);
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

    /**
     * Get route for PUT requests
     *
     * @param String $service service id
     *
     * @return Route
     */
    public static function getRoutePut($service)
    {
        $pattern = '/'.static::getBaseFromService($service).'/{id}';
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

    /**
     * Get route for DELETE requests
     *
     * @param String $service service id
     *
     * @return Route
     */
    public static function getRouteDelete($service)
    {
        $pattern = '/'.static::getBaseFromService($service).'/{id}';
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

    /**
     * Get entity name from service strings.
     *
     * By convention the last part of the service string so far
     * makes up the entities name.
     *
     * @param String $service (partial) service id
     *
     * @return String
     */
    private static function getBaseFromService($service)
    {
        $parts = explode('.', $service);

        $entity = array_pop($parts);
        $module = $parts[1];

        return '/'.$module.'/'.$entity;
    }
}
