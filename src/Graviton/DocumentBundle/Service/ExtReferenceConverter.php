<?php
/**
 * ExtReferenceConverter class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\DocumentBundle\Entity\ExtReference;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;

/**
 * Extref converter
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceConverter implements ExtReferenceConverterInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var array
     */
    private $mapping;

    /**
     * Constructor
     *
     * @param RouterInterface $router  Router
     * @param array           $mapping colleciton_name => service_id mapping
     */
    public function __construct(RouterInterface $router, array $mapping)
    {
        $this->router = $router;
        $this->mapping = $mapping;
    }

    /**
     * return the extref from URL
     *
     * @param string $url Extref URL
     * @return ExtReference
     * @throws \InvalidArgumentException
     */
    public function getExtReference($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === false) {
            throw new \InvalidArgumentException(sprintf('URL %s', $url));
        }

        $id = null;
        $collection = null;

        foreach ($this->router->getRouteCollection()->all() as $route) {
            list($collection, $id) = $this->getDataFromRoute($route, $path);
            if ($collection !== null && $id !== null) {
                break;
            }
        }

        if ($collection === null || $id === null) {
            throw new \InvalidArgumentException(sprintf('Could not read URL %s', $url));
        }

        return ExtReference::create($collection, $id);
    }

    /**
     * return the URL from extref
     *
     * @param ExtReference $extReference Extref
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrl(ExtReference $extReference)
    {
        if (!isset($this->mapping[$extReference->getRef()])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not create URL from extref "%s"',
                    json_encode($extReference)
                )
            );
        }

        return $this->router->generate(
            $this->mapping[$extReference->getRef()].'.get',
            ['id' => $extReference->getId()],
            true
        );
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
            $serviceName = implode('.', [$core, $bundle, 'rest', $name]);
            $collection = array_search($serviceName, $this->mapping);

            return [$collection, $id];
        }

        return [null, null];
    }
}
