<?php
/**
 * handle mongodb references that get exposed as external link
 */

namespace Graviton\RestBundle\Subscriber;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Context;

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
    private static $types;

    /**
     * @param Router $router router used for generating links
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Router $router router used for generating links
     * @param array $types array of types this handler subscribes to
     */
    public static function getTypedHandler(Router $router, array $types)
    {
        self::$types = $types;
        return new self($router);
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array();
        $defaults = array (
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format' => 'json',
            'method' => 'serialize',
        );

        $methods = array();

        foreach (self::$types as $type) {
            $methods[] = array_merge(array('type' => $type), $defaults);
        }

        return $methods;
    }

    /**
     * @param JsonSerializationVisitor $visitor  jms_serializer listener
     * @param object                   $document document to serialize
     *
     * @return object
     */
    public function serialize(JsonSerializationVisitor $visitor, $document)
    {
        $id = $document->getRef()->getId();

        $link = $this->router->generate(
            'graviton.core.rest.app.get',
            array(
                'id' => $id,
            ),
            true
        );
        return array(
            'id' => $id,
            '$ref' => $link,
        );
    }
}
