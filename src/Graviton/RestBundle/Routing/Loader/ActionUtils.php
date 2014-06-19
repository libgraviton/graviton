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
class ActionUtils
{
    /**
     * Get route for GET requests
     *
     * @param string $service service id
     *
     * @return Route
     */
    public static function getRouteGet($service)
    {
        return self::getRoute($service, 'GET', 'getAction', array('id' => '\w+'));
    }

    /**
     * Get route for getAll requests
     *
     * @param string $service service id
     *
     * @return Route
     */
    public static function getRouteAll($service)
    {
        return self::getRoute($service, 'GET', 'allAction');
    }

    /**
     * Get route for POST requests
     *
     * @param string $service service id
     *
     * @return Route
     */
    public static function getRoutePost($service)
    {
        return self::getRoute($service, 'POST', 'postAction');
    }

    /**
     * Get route for PUT requests
     *
     * @param string $service service id
     *
     * @return Route
     */
    public static function getRoutePut($service)
    {
        return self::getRoute($service, 'PUT', 'putAction', array('id' => '\w+'));
    }
    
    /**
     * Get route for PATCH request
     * 
     * @param string $service service id
     */
    public static function getRoutePatch($service)
    {
    	return self::getRoute($service, 'PATCH', 'patchAction', array('id' => '\w+'));
    }

    /**
     * Get route for DELETE requests
     *
     * @param string $service service id
     *
     * @return Route
     */
    public static function getRouteDelete($service)
    {
        return self::getRoute($service, 'DELETE', 'deleteAction', array('id' => '\w+'));
    }

    /**
     * Get route for OPTIONS requests
     *
     * @param string $service    service id
     * @param array  $parameters service params
     *
     * @return Route
     */
    public static function getRouteOptions($service, array $parameters = array())
    {
        return self::getRoute($service, 'OPTIONS', 'optionsAction', $parameters);
    }

    /**
     * Get canonical route for schema requests
     *
     * @param string $service service id
     * @param string $type    service type (item or collection)
     *
     * @return Route
     */
    public static function getCanonicalSchemaRoute($service, $type = 'item')
    {
        $pattern = self::getBaseFromService($service);
        $pattern = '/schema'.$pattern.'/'.$type;

        $defaults = array(
            '_controller' => $service.':optionsAction',
            '_format' => '~',
        );

        $requirements = array(
            '_method' => 'GET',
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
     * @param string $service (partial) service id
     *
     * @return string
     */
    private static function getBaseFromService($service)
    {
        $parts = explode('.', $service);

        $entity = array_pop($parts);
        $module = $parts[1];

        return '/'.$module.'/'.$entity;
    }

    /**
     * Get Route
     *
     * @param string $service    name of service containing controller
     * @param string $method     HTTP method to generate route for
     * @param string $action     action to call for route
     * @param array  $parameters route parameters to append to route as pair of name and patterns
     *
     * @return Route
     */
    private static function getRoute($service, $method, $action, $parameters = array())
    {
        $pattern = self::getBaseFromService($service);
        $defaults = array(
            '_controller' => $service.':'.$action,
            '_format' => '~',
        );

        $requirements = array(
            '_method' => $method,
        );

        foreach ($parameters as $paramName => $paramPattern) {
            $pattern .= '/{'.$paramName.'}';
            $requirements[$paramName] = $paramPattern;
        }

        $route = new Route($pattern, $defaults, $requirements);

        return $route;
    }
}
