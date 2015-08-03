<?php
/**
 * doctrine custom type to handle reading and writing $refs attributes
 */

namespace Graviton\DocumentBundle\Types;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Route;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * based on http://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/basic-mapping.html#custom-mapping-types
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReference extends Type
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $mapping;

    /**
     * inject a router
     *
     * This uses setter injection due to the fact that doctrine doesn't do constructor injection
     *
     * @param Router $router router
     *
     * @return void
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * inject collection name to routing service mapping
     *
     * @param array $mapping colleciton_name => service_id mapping
     *
     * @return void
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * get php value when field is used as identifier
     *
     * @param \MongoDBRef $value ref from mongodb
     *
     * @return string
     */
    public function convertToPHPValue($value)
    {
        if (!array_key_exists('$ref', $value)
            && !array_key_exists($value['$ref'], $this->mapping)
            && !array_key_exists('$id', $value)
        ) {
            return '';
        }
        return $this->router->generate($this->mapping[$value['$ref']], ['id' => $value['$id']]);
    }

    /**
     * return a closure as string that sets $return if field is a regular field
     *
     * @return string
     */
    public function closureToPHP()
    {
        // return full value for later processing since we do not have mappings during hydrator generation
        return '$return = json_encode($value);';
    }

    /**
     * return the mongodb representation from a php value
     *
     * @param string $value value of reference as URI
     *
     * @return array
     */
    public function convertToDatabaseValue($value)
    {
        if (empty($this->router)) {
            throw new \RuntimeException('no router injected into '.__CLASS__);
        }
        if (empty($value)) {
            throw new \RuntimeException('Empty URL in '.__CLASS__);
        }

        $path = $this->getPathFromUrl($value);

        $id = null;
        $collection = null;

        foreach ($this->router->getRouteCollection()->all() as $route) {
            list($collection, $id) = $this->getDataFromRoute($route, $path);
            if ($collection !== null && $id !== null) {
                break;
            }
        }

        if ($collection === null || $id === null) {
            throw new \RuntimeException(sprintf('Could not read URL %s', $value));
        }

        return \MongoDBRef::create($collection, $id);
    }

    /**
     * get path from url
     *
     * @param string $url url from request
     *
     * @return string
     */
    private function getPathFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === false) {
            throw new \RuntimeException('No path found in URL '.$url);
        }
        return $path;
    }


    /**
     * get collection and id from route
     *
     * @param Route  $route route to look at
     * @param string $value value of reference as URI
     *
     * @return array
     */
    private function getDataFromRoute(Route $route, $value)
    {
        if ($route->getRequirement('id') !== null &&
            $route->getMethods() === ['GET'] &&
            preg_match($route->compile()->getRegex(), $value, $matches)
        ) {
            $id = $matches['id'];

            list($routeService) = explode(':', $route->getDefault('_controller'));
            list($core, $bundle,,$name) = explode('.', $routeService);
            $serviceName = implode('.', [$core, $bundle, 'rest', $name, 'get']);
            $collection = array_search($serviceName, $this->mapping);

            return [$collection, $id];
        }

        return [null, null];
    }
}
