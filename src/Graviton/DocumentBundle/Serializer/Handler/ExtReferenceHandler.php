<?php
/**
 * ExtReferenceHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * JMS serializer handler for ExtReference
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceHandler
{

    /**
     * Constructor
     *
     * @param ExtReferenceConverter $converter Converter
     */
    public function __construct(private readonly ExtReferenceConverter $converter)
    {
    }

    /**
     * Serialize extref to JSON
     *
     * @param SerializationVisitorInterface $visitor      Visitor
     * @param ExtReference                  $extReference Extref
     * @param array                         $type         Type
     * @param Context                       $context      Context
     * @return string|null
     */
    public function serializeExtReferenceToJson(
        SerializationVisitorInterface $visitor,
        ExtReference $extReference,
        array $type,
        Context $context
    ) {
        if (null === $extReference && !$context->shouldSerializeNull()) {
            throw new NotAcceptableException();
        }

        if (null === $extReference) {
            return $visitor->visitNull(null, $type);
        }

        try {
            return $this->converter->getUrl($extReference);
        } catch (\InvalidArgumentException $e) {
            return $visitor->visitNull(null, $type);
        }
    }

    /**
     * Serialize extref to JSON
     *
     * @param DeserializationVisitorInterface $visitor Visitor
     * @param string                          $url     Extref URL
     * @param array                           $type    Type
     * @param Context                         $context Context
     * @return ExtReference|null
     */
    public function deserializeExtReferenceFromJson(
        DeserializationVisitorInterface $visitor,
        $url,
        array $type,
        Context $context
    ) {
        if (null === $url && !$context->shouldSerializeNull()) {
            throw new NotAcceptableException();
        }

        if (null === $url) {
            return $visitor->visitNull(null, $type);
        }

        return $this->converter->getExtReference(
            $visitor->visitString($url, $type, $context)
        );
    }
}
