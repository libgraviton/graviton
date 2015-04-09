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

        if (substr($value, 0, 4) == 'http') {
            $value = parse_url($value, PHP_URL_PATH);
        }

        foreach ($this->router->getRouteCollection()->all() as $route) {
            if (!empty($collection) && !empty($id)) {
                return \MongoDBRef::create($collection, $id);
            }
            list($collection, $id) = $this->getDataFromRoute($route, $value);
        }

        if (empty($collection) || empty($id)) {
            throw new \RuntimeException(sprintf('Could not read URL %s', $value));
        }
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
        $collection = null;
        $id = null;

        $reqs = $route->getRequirements();
        $keys = array_filter(
            array_keys($reqs),
            function ($req) {
                return substr($req, 0, 1) !== '_';
            }
        );

        $params = array();
        foreach ($keys as $key) {
            $params[$key] = $reqs[$key];
        }

        $matches = [];
        if (preg_match($route->compile()->getRegex(), $value, $matches)) {
            $id = $matches['id'];

            list($routeService) = explode(':', $route->getDefault('_controller'));
            list(,,,$name) = explode('.', $routeService);
            $collection = ucfirst($name);
        }

        return [$collection, $id];
    }
}
