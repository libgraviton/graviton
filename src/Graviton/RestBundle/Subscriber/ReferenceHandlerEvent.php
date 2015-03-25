<?php
/**
 * handle mongodb references that get exposed as external link
 */

namespace Graviton\RestBundle\Subscriber;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class ReferenceHandlerEvent implements SubscribingHandlerInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private static $types = array();

    /**
     * @param Router    $router    router used for generating links
     * @param Container $container Symfony DIC
     */
    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->parameterBag = $container->getParameterBag();
        $this->container = $container;
    }

    /**
     * @param Router    $router    router used for generating links
     * @param Container $container Symfony DIC
     * @param array     $types     array of types this handler subscribes to
     *
     * @return \Graviton\RestBundle\Subscriber\ReferenceHandlerEvent
     */
    public static function getTypedHandler(Router $router, Container $container, array $types)
    {
        self::$types = $types;
        return new self($router, $container);
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array();
        // $defaults = array (
        //     'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
        //     'format' => 'json',
        //     'method' => 'serialize',
        // );
        //
        // $methods = array();
        //
        // foreach (self::$types as $type) {
        //     $methods[] = array_merge(array('type' => $type), $defaults);
        // }
        //
        // return $methods;
    }

    /**
     * @param JsonSerializationVisitor $visitor  jms_serializer listener
     * @param object                   $document document to serialize
     * @param array                    $type     foo??
     *
     * @return object
     */
    public function serialize(JsonSerializationVisitor $visitor, $document, $type)
    {
        $id = $document->getRef()->getId();

        try {
            list($prefix, $bundle,) = explode('\\', strtolower($type['name']));
            $docName = str_replace('bundle', '', $bundle);
            $parameter = sprintf('%s.%s.relations', $prefix, $docName);

            $relations = $this->parameterBag->get($parameter);
            $pathInfo = sprintf('%s%s', array_shift($relations), $id);

            $matcher = $this->router->getMatcher();
            $route = $matcher->match($pathInfo);

            $link = $this->router->generate(
                $route['_route'],
                array(
                    'id' => $id,
                ),
                true
            );

        } catch (ParameterNotFoundException $e) {
            return array(
                'id' => $id,
                '$ref' => sprintf('Parameter (%s) is not registered.', $parameter),
            );
        } catch (ResourceNotFoundException $e) {
            return array(
                'id' => $id,
                '$ref' => sprintf('Could not resolve route (%s).', $pathInfo),
            );

        }

        return array(
            'id' => $id,
            '$ref' => $link,
        );
    }
}
