<?php
/**
 * HashHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use JMS\Serializer\Context;
use Graviton\DocumentBundle\Entity\Hash;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hash handler for JMS serializer
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashHandler
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $seenCounter = [];

    /**
     * @var null|object
     */
    private $currentRequestContent = null;

    /**
     * HashHandler constructor.
     *
     * @param RequestStack $requestStack request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Serialize Hash object
     *
     * @param SerializationVisitorInterface $visitor Visitor
     * @param Hash                          $data    Data
     * @param array                         $type    Type
     * @param Context                       $context Context
     * @return Hash
     */
    public function serializeHashToJson(
        SerializationVisitorInterface $visitor,
        Hash $data,
        array $type,
        Context $context
    ) {
        return $data;
    }

    /**
     * Deserialize Hash object
     *
     * @param DeserializationVisitorInterface $visitor Visitor
     * @param array                           $data    Data
     * @param array                           $type    Type
     * @param Context                         $context Context
     * @return Hash
     */
    public function deserializeHashFromJson(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        Context $context
    ) {
        return new Hash($data);
    }

    /**
     * returns the json_decoded content of the current request. if there is no request, it
     * will return null
     *
     * @return mixed|null|object the json_decoded request content
     */
    private function getCurrentRequestContent()
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!is_null($currentRequest)) {
            $this->currentRequestContent = json_decode($currentRequest->getContent());
        }
        return $this->currentRequestContent;
    }

    /**
     * this checks for a special case which this new approach is really flawed. if this
     * handler is used to deserialize an array, we are not aware of the current index in the iteration.
     * so we record for what we have been called how many times ($this->seenCounter) and
     * if this function here returns true, we only return the index specified by the seencounter.
     *
     * we assume that we are in this special case when the userData (the one parsed from the request)
     * has sequential keys *and* they are different from the keys that the serializer data gives us.
     *
     * @param array $userArr       data from users request
     * @param array $serializerArr data from the serializer
     *
     * @return bool true if yes, false otherwise
     */
    private function isSequentialArrayCase($userArr, $serializerArr)
    {
        return (
            is_array($userArr) &&
            (array_keys($userArr) == range(0, count($userArr) - 1)) &&
            array_keys($userArr) != array_keys($serializerArr)
        );
    }

    /**
     * convenience function for the location counting for the "sequential array case" as described above.
     *
     * @param array $location the current path from the serializer
     *
     * @return int the counter
     */
    private function getLocationCounter($location)
    {
        $locationHash = md5(implode(',', $location));
        if (!isset($this->seenCounter[$locationHash])) {
            $this->seenCounter[$locationHash] = 0;
        } else {
            $this->seenCounter[$locationHash]++;
        }
        return $this->seenCounter[$locationHash];
    }
}
