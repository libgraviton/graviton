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
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public static function getSubscribingMethods()
    {
        return array(
            array (
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => 'GravitonDyn\ModuleBundle\Document\ModuleApp',
                'format' => 'json',
                'method' => 'serialize',
            ),
            array (
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => 'link',
                'format' => 'json',
                'method' => 'deserialize',
            ),
        );
    }

    public function serialize(JsonSerializationVisitor $visitor, $document, $type, $context)
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
    public function deserialize(Visitor\ArrayDeserialize $visitor, $document, array $type, Context $context)
    {
        return $document;
    }
}
