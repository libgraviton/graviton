<?php
/**
 * Load routes for all rest services
 */

namespace Graviton\RestBundle\Routing\Loader;

use Graviton\CoreBundle\Routing\RouteLoaderAbstract;
use Symfony\Component\Routing\RouteCollection;

/**
 * Load routes for all rest services
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BasicLoader extends RouteLoaderAbstract
{
    /**
     * @var boolean
     */
    private $loaded = false;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $routes;

    /**
     * @var array
     */
    private $services;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Routing\RouteCollection $routes   route collection
     * @param array                                      $services configs for all services tagged as graviton.rest
     */
    public function __construct($routes, $services)
    {
        $this->routes = $routes;
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
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "graviton.rest.routing.loader" loader twice');
        }

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

        $this->loaded = true;

        return $this->routes;
    }

    /**
     * returns the name of the resource to load
     *
     * @return string resource name
     */
    public function getResourceName()
    {
        return 'rest';
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
        list($app, $bundle, , $entity) = explode('.', $service);
        $resource = implode('.', array($app, $bundle, 'rest', $entity));

        $this->loadReadOnlyRoutes($service, $resource, $serviceConfig);
        if (!($serviceConfig[0] && array_key_exists('read-only', $serviceConfig[0]))) {
            $this->loadWriteRoutes($service, $resource, $serviceConfig);
        }
    }

    /**
     * generate ro routes
     *
     * @param string $service       service name
     * @param string $resource      resource name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    public function loadReadOnlyRoutes($service, $resource, $serviceConfig)
    {
        $actionOptions = ActionUtils::getRouteOptions($service, $serviceConfig);
        $this->routes->add($resource . '.options', $actionOptions);

        $actionOptionsNoSlash = ActionUtils::getRouteOptions($service, $serviceConfig);
        $actionOptionsNoSlash->setPath(substr($actionOptionsNoSlash->getPath(), 0, -1));
        $this->routes->add($resource . '.optionsNoSlash', $actionOptionsNoSlash);

        $actionHead = ActionUtils::getRouteHead($service, $serviceConfig);
        $this->routes->add($resource . '.head', $actionHead);

        $actionHead = ActionUtils::getRouteHead($service, $serviceConfig, [], true);
        $this->routes->add($resource . '.idHead', $actionHead);

        $actionGet = ActionUtils::getRouteGet($service, $serviceConfig);
        $this->routes->add($resource . '.get', $actionGet);

        $actionAll = ActionUtils::getRouteAll($service, $serviceConfig);
        $this->routes->add($resource . '.all', $actionAll);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'collection');
        $this->routes->add($resource . '.canonicalSchema', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'collection', true);
        $this->routes->add($resource . '.canonicalSchemaOptions', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig);
        $this->routes->add($resource . '.canonicalIdSchema', $actionOptions);

        $actionOptions = ActionUtils::getCanonicalSchemaRoute($service, $serviceConfig, 'item', true);
        $this->routes->add($resource . '.canonicalIdSchemaOptions', $actionOptions);

        $actionOptions = ActionUtils::getRouteOptions($service, $serviceConfig, [], true);
        $this->routes->add($resource . '.idOptions', $actionOptions);
    }

    /**
     * generate write routes
     *
     * @param string $service       service name
     * @param string $resource      resource name
     * @param array  $serviceConfig service configuration
     *
     * @return void
     */
    public function loadWriteRoutes($service, $resource, $serviceConfig)
    {
        $actionPost = ActionUtils::getRoutePost($service, $serviceConfig);
        $this->routes->add($resource . '.post', $actionPost);

        $actionPut = ActionUtils::getRoutePut($service, $serviceConfig);
        $this->routes->add($resource . '.put', $actionPut);

        $actionPostNoSlash = ActionUtils::getRoutePost($service, $serviceConfig);
        $actionPostNoSlash->setPath(substr($actionPostNoSlash->getPath(), 0, -1));
        $this->routes->add($resource . '.postNoSlash', $actionPostNoSlash);

        $actionPatch = ActionUtils::getRoutePatch($service, $serviceConfig);
        $this->routes->add($resource . '.patch', $actionPatch);

        $actionDelete = ActionUtils::getRouteDelete($service, $serviceConfig);
        $this->routes->add($resource . '.delete', $actionDelete);
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
}
