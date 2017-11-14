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
        $dataObj = null;

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!is_null($currentRequest)) {
            $dataObj = json_decode($currentRequest->getContent());
            foreach ($currentPath as $pathElement) {
                $dataObj = $dataObj->{$pathElement};
            }
        }

        if (!is_null($dataObj)) {
            return new Hash($dataObj);
        }

        return new Hash($visitor->visitArray($data, $type, $context));
    }
}
