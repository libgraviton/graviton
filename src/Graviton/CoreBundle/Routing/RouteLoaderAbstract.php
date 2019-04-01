<?php
/**
 * Abstract for RouteLoaders that get loaded through our internal Graviton RouteLoader
 */

namespace Graviton\CoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class RouteLoaderAbstract extends Loader
{

    /**
     * returns the name of the resource to load
     *
     * @return string resource name
     */
    abstract public function getResourceName();

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
        return new RouteCollection();
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
        return ($resource == $this->getResourceName());
    }

    /**
     * return a yaml file path here if this one should be loaded
     *
     * @return null|string path to yaml file
     */
    public function loadYamlFile()
    {
        return null;
    }
}
