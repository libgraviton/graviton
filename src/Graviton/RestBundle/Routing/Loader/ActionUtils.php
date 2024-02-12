<?php
/**
 * Generate routes for individual actions
 */

namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Routing\Route;

/**
 * Generate routes for individual actions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ActionUtils
{
    const ID_PATTERN = '[a-zA-Z0-9\-_\+\040\'\.]+';

    /**
     * Get route for GET requests
     *
     * @param string $service       service id
     * @param array  $serviceConfig service configuration
     *
     * @return Route
     */
    public static function getRouteGet($service, $serviceConfig)
    {
        return self::getRoute($service, 'GET', 'getAction', $serviceConfig, array('id' => self::ID_PATTERN));
    }

    /**
     * Get Route
     *
     * @param string $service       name of service containing controller
     * @param string $method        HTTP method to generate route for
     * @param string $action        action to call for route
     * @param array  $serviceConfig service configuration
     * @param array  $parameters    route parameters to append to route as pair of name and patterns
     *
     * @return Route
     */
    private static function getRoute($service, $method, $action, $serviceConfig, $parameters = array())
    {
        $pattern = self::getBaseFromService($service, $serviceConfig);

        $defaults = array(
            '_controller' => $service . '::' . $action,
            '_format' => '~',
        );

        // add service params as defaults..
        if (!empty($serviceConfig[0]) && is_array($serviceConfig[0])) {
            $defaults = array_merge($serviceConfig[0], $defaults);
        }

        $requirements = [];

        foreach ($parameters as $paramName => $paramPattern) {
            $pattern .= '{' . $paramName . '}';
            $requirements[$paramName] = $paramPattern;
        }

        $route = new Route($pattern, $defaults, $requirements);
        $route->setMethods($method);

        return $route;
    }

    /**
     * Get entity name from service strings.
     *
     * By convention the last part of the service string so far
     * makes up the entities name.
     *
     * @param string $service       (partial) service id
     * @param array  $serviceConfig service configuration
     *
     * @return string
     */
    private static function getBaseFromService($service, $serviceConfig)
    {
        if (isset($serviceConfig[0]['router-base']) && strlen($serviceConfig[0]['router-base']) > 0) {
            $base = $serviceConfig[0]['router-base'];
            if (!str_ends_with($base, '/')) {
                $base .= '/';
            }
        } else {
            $parts = explode('.', $service);

            $entity = array_pop($parts);
            $module = $parts[1];

            $base = '/' . $module . '/' . $entity . '/';
        }

        return $base;
    }

    /**
     * Get route for getAll requests
     *
     * @param string $service       service id
     * @param array  $serviceConfig service configuration
     *
     * @return Route
     */
    public static function getRouteAll($service, $serviceConfig)
    {
        return self::getRoute($service, 'GET', 'allAction', $serviceConfig);
    }

    /**
     * Get route for POST requests
     *
     * @param string $service       service id
     * @param array  $serviceConfig service configuration
     *
     * @return Route
     */
    public static function getRoutePost($service, $serviceConfig)
    {
        return self::getRoute($service, 'POST', 'postAction', $serviceConfig);
    }

    /**
     * Get route for PUT requests
     *
     * @param string $service       service id
     * @param array  $serviceConfig service configuration
     *
     * @return Route
     */
    public static function getRoutePut($service, $serviceConfig)
    {
        return self::getRoute($service, 'PUT', 'putAction', $serviceConfig, array('id' => self::ID_PATTERN));
    }

    /**
     * Get route for PATCH requests
     *
     * @param string $service       service id
     * @param array  $serviceConfig service configuration
     *
     * @return Route
     */
    public static function getRoutePatch($service, $serviceConfig)
    {
        return self::getRoute($service, 'PATCH', 'patchAction', $serviceConfig, array('id' => self::ID_PATTERN));
    }

    /**
     * Get route for DELETE requests
     *
     * @param string $service       service id
     * @param array  $serviceConfig service configuration
     *
     * @return Route
     */
    public static function getRouteDelete($service, $serviceConfig)
    {
        return self::getRoute($service, 'DELETE', 'deleteAction', $serviceConfig, array('id' => self::ID_PATTERN));
    }

    /**
     * Get route for OPTIONS requests
     *
     * @param string  $service       service id
     * @param array   $serviceConfig service configuration
     * @param array   $parameters    service params
     * @param boolean $useIdPattern  generate route with id param
     *
     * @return Route
     */
    public static function getRouteOptions($service, $serviceConfig, array $parameters = [], $useIdPattern = false)
    {
        if ($useIdPattern) {
            $parameters['id'] = self::ID_PATTERN;
        }
        return self::getRoute($service, ['OPTIONS', 'HEAD'], 'optionsAction', $serviceConfig, $parameters);
    }

    /**
     * Get canonical route for schema requests
     *
     * @param string  $service       service id
     * @param array   $serviceConfig service configuration
     * @param string  $format        format
     * @param boolean $option        render a options route
     *
     * @return Route
     */
    public static function getCanonicalSchemaRoute(string $service, array $serviceConfig, string $format, bool $option)
    {
        $pattern = self::getBaseFromService($service, $serviceConfig);
        if (!str_ends_with($pattern, '/')) {
            $pattern .= '/';
        }

        $pattern = '/schema' . $pattern . 'openapi.' . $format;

        $action = 'schemaAction';
        $method = 'GET';

        if ($option !== false) {
            $action = 'optionsAction';
            $method = 'OPTIONS';
        }

        $defaults = array(
            '_controller' => $service . '::' . $action,
            '_format' => '~',
        );

        if (!empty($serviceConfig[0]) && is_array($serviceConfig[0])) {
            $defaults = array_merge($serviceConfig[0], $defaults);
        }

        $route = new Route($pattern, $defaults, []);
        $route->setMethods($method);

        return $route;
    }
}
