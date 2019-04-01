<?php
/**
 * Chain loads other RouteLoaders
 */

namespace Graviton\CoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RouteLoader extends Loader
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $serviceIds;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container  service container
     * @param array              $serviceIds ids of route loader services
     */
    public function __construct(ContainerInterface $container, array $serviceIds = [])
    {
        $this->container = $container;
        $this->serviceIds = $serviceIds;
    }

    /**
     * Load routes for all services tagged with graviton.rest
     *
     * @param string $resource resource name
     * @param string $type     type
     *
     * @return RouteCollection routes
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        foreach (array_keys($this->serviceIds) as $serviceId) {
            $routeLoader = $this->container->get($serviceId);
            if ($routeLoader instanceof RouteLoaderAbstract) {
                $yamlFile = $routeLoader->loadYamlFile();
                if (is_null($yamlFile)) {
                    $routes->addCollection($routeLoader->load($routeLoader->getResourceName()));
                } else {
                    $routes->addCollection($this->import($yamlFile, 'yaml'));
                }
            }
        }

        return $routes;
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
        return 'graviton.core.route_loader' === $type;
    }
}
