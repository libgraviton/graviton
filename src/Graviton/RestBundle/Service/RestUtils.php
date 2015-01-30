<?php

namespace Graviton\RestBundle\Service;


class RestUtils
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * sets the container
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer($container = null)
    {
        $this->container = $container;
    }

    /**
     * Builds a map of baseroutes (controllers) to its relevant route to the actions.
     * ignores schema stuff.
     *
     * @return array grouped array of basenames and actions..
     */
    public function getServiceRoutingMap()
    {
        $ret = array();
        $optionRoutes = $this->getOptionRoutes();

        foreach ($optionRoutes as $routeName => $optionRoute) {
            // get base name from options action
            $routeParts = explode('.', $routeName);
            array_pop($routeParts); // get rid of last part
            $baseName = implode('.', $routeParts);

            // get routes from same controller
            foreach ($this->getRoutesByBasename($baseName) as $routeName => $route) {
                // don't put schema stuff
                if (strpos('schema', strtolower($routeName)) === false) {
                    $ret[$baseName][$routeName] = $route;
                }
            }
        }

        return $ret;
    }

    /**
     * It has been deemed that we search for OPTION routes in order to detect our
     * service routes and then derive the rest from them.
     *
     * @return array An array with option routes
     */
    public function getOptionRoutes()
    {
        $router = $this->container->get('router');
        $ret = array_filter(
            $router->getRouteCollection()
                   ->all(),
            function ($route) {
                if ($route->getRequirement('_method') != 'OPTIONS') {
                    return false;
                }

                return is_null($route->getRequirement('id'));
            }
        );

        return $ret;
    }

    /**
     * Based on $baseName, this function returns all routes that match this basename..
     * So if you pass graviton.cont.action; it will return all route names that start with the same.
     * In our routing naming schema, this means all the routes from the same controller.
     *
     * @param $baseName string basename
     *
     * @return array array with matching routes
     */
    public function getRoutesByBasename($baseName)
    {
        $ret = array();
        foreach ($this->container->get('router')->getRouteCollection()->all() as $routeName => $route) {
            if (preg_match('/^'.$baseName.'/', $routeName)) {
                $ret[$routeName] = $route;
            }
        }
        return $ret;
    }
}
