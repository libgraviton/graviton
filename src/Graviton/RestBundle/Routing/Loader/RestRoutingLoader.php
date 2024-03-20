<?php
/**
 * Load routes for all rest services
 */

namespace Graviton\RestBundle\Routing\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Load routes for all rest services
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestRoutingLoader extends Loader
{

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var array
     */
    private $services;

    /**
     * Constructor.
     *
     * @param array $services configs for all services tagged as graviton.rest
     */
    public function __construct($services)
    {
        $this->routes = new RouteCollection();
        $this->services = $services;
    }

    /**
     * Load routes for all services tagged with graviton.rest
     *
     * @param string $resource unused
     * @param string $type     unused
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null): mixed
    {
        // sort by path length (so longer ones are first in case of overlap)
        uasort(
            $this->services,
            function ($a, $b) {
                if (!isset($a[0]['router-base']) || !isset($b[0]['router-base'])) {
                    return 1;
                }

                $aLength = strlen($a[0]['router-base']);
                $bLength = strlen($b[0]['router-base']);

                if ($aLength == $bLength) {
                    return 0;
                }
                return ($aLength < $bLength) ? 1 : -1;
            }
        );

        foreach ($this->services as $service => $serviceConfig) {
            $this->loadService($service, $serviceConfig);
        }

        // add our label!
        array_map(
            function (Route $route) {
                $route->addDefaults(['graviton-rest' => true]);
            },
            $this->routes->all()
        );

        return $this->routes;
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
        if (!is_array($serviceConfig[0])) {
            return;
        }

        if (!isset($serviceConfig[0]['collection']) || !isset($serviceConfig[0]['router-base'])) {
            // stuff missing?
            return;
        }

        $entityName = $serviceConfig[0]['collection'];
        $readOnly = array_key_exists('read-only', $serviceConfig[0]) && $serviceConfig[0]['read-only'] == true;

        $this->loadReadOnlyRoutes($service, $entityName, $serviceConfig);
        if (!$readOnly) {
            $this->loadWriteRoutes($service, $entityName, $serviceConfig);
        }
    }

    /**
     * generate ro routes
     *
     * @param string $service       service name
     * @param string $entityName    resource name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    private function loadReadOnlyRoutes(string $service, string $entityName, array $serviceConfig)
    {
        $actionOptions = ActionUtils::getRouteOptions($service, $serviceConfig);
        $this->routes->add($entityName . '.options', $actionOptions);

        $actionOptionsNoSlash = ActionUtils::getRouteOptions($service, $serviceConfig);
        $actionOptionsNoSlash->setPath(substr($actionOptionsNoSlash->getPath(), 0, -1));
        $this->routes->add($entityName . '.optionsNoSlash', $actionOptionsNoSlash);

        $actionGet = ActionUtils::getRouteGet($service, $serviceConfig);
        $this->routes->add($entityName . '.get', $actionGet);

        $actionAll = ActionUtils::getRouteAll($service, $serviceConfig);
        $this->routes->add($entityName . '.all', $actionAll);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'json', false);
        $this->routes->add($entityName . '.schemaJsonGet', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'json', true);
        $this->routes->add($entityName . '.schemaJsonOptions', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'yaml', false);
        $this->routes->add($entityName . '.schemaYamlGet', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'yaml', true);
        $this->routes->add($entityName . '.schemaYamlOptions', $actionOptions);

        $actionOptions = ActionUtils::getRouteOptions($service, $serviceConfig, [], true);
        $this->routes->add($entityName . '.idOptions', $actionOptions);
    }

    /**
     * generate write routes
     *
     * @param string $service       service name
     * @param string $entityName    resource name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    private function loadWriteRoutes(string $service, string $entityName, array $serviceConfig)
    {
        $actionPost = ActionUtils::getRoutePost($service, $serviceConfig);
        $this->routes->add($entityName . '.post', $actionPost);

        $actionPut = ActionUtils::getRoutePut($service, $serviceConfig);
        $this->routes->add($entityName . '.put', $actionPut);

        $actionPostNoSlash = ActionUtils::getRoutePost($service, $serviceConfig);
        $actionPostNoSlash->setPath(substr($actionPostNoSlash->getPath(), 0, -1));
        $this->routes->add($entityName . '.postNoSlash', $actionPostNoSlash);

        $actionPatch = ActionUtils::getRoutePatch($service, $serviceConfig);
        $this->routes->add($entityName . '.patch', $actionPatch);

        $actionDelete = ActionUtils::getRouteDelete($service, $serviceConfig);
        $this->routes->add($entityName . '.delete', $actionDelete);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $resource unused
     * @param string $type     Type to match against
     *
     * @return Boolean
     */
    public function supports($resource, $type = null): bool
    {
        return 'graviton.rest.route_loader' === $type;
    }
}
