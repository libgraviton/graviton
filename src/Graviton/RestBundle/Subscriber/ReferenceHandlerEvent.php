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

    /**
     * @param Router $router router used for generating links
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array (
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => 'GravitonDyn\ModuleBundle\Document\ModuleApp',
                'format' => 'json',
                'method' => 'serialize',
            )
        );
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
