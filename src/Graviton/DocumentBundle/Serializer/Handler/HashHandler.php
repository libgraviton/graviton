<?php
/**
 * HashHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use Graviton\DocumentBundle\Entity\Hash;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hash handler for JMS serializer
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HashHandler
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Serialize Hash object
     *
     * @param JsonSerializationVisitor $visitor Visitor
     * @param Hash                     $data    Data
     * @param array                    $type    Type
     * @param Context                  $context Context
     * @return Hash
     */
    public function serializeHashToJson(
        JsonSerializationVisitor $visitor,
        Hash $data,
        array $type,
        Context $context
    ) {
        return new Hash($data);
    }

    /**
     * Deserialize Hash object
     *
     * @param JsonDeserializationVisitor $visitor Visitor
     * @param array                      $data    Data
     * @param array                      $type    Type
     * @param Context                    $context Context
     * @return Hash
     */
    public function deserializeHashFromJson(
        JsonDeserializationVisitor $visitor,
        array $data,
        array $type,
        Context $context
    ) {
        $currentPath = $context->getCurrentPath();
        $currentRequest = $this->requestStack->getCurrentRequest();
        $dataObj = null;

        var_dump($data);
        var_dump($type);
        var_dump($currentPath);
        var_dump($context->attributes);
        echo "-----------";

        if (!is_null($currentRequest)) {
            $dataObj = json_decode($currentRequest->getContent());
            foreach ($currentPath as $pathElement) {
                if (isset($dataObj->{$pathElement})) {
                    $dataObj = $dataObj->{$pathElement};
                } else {
                    $dataObj = null;
                    break;
                }

            }
        }

        if (!is_null($dataObj)) {
            $data = $dataObj;
        }

        //return new Hash($data);


        return new Hash($visitor->visitArray((array) $data, $type, $context));
    }
}
