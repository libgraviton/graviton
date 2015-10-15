<?php
/**
 * ExtReferenceHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * JMS serializer handler for ExtReference
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceHandler
{
    /**
     * @var ExtReferenceConverterInterface
     */
    private $converter;

    /**
     * Constructor
     *
     * @param ExtReferenceConverterInterface $converter Converter
     */
    public function __construct(ExtReferenceConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Serialize extref to JSON
     *
     * @param JsonSerializationVisitor $visitor      Visitor
     * @param ExtReference             $extReference Extref
     * @param array                    $type         Type
     * @param Context                  $context      Context
     * @return string|null
     */
    public function serializeExtReferenceToJson(
        JsonSerializationVisitor $visitor,
        ExtReference $extReference,
        array $type,
        Context $context
    ) {
        try {
            return $visitor->visitString($this->converter->getUrl($extReference), $type, $context);
        } catch (\InvalidArgumentException $e) {
            return $visitor->visitNull(null, $type, $context);
        }
    }

    /**
     * Serialize extref to JSON
     *
     * @param JsonDeserializationVisitor $visitor Visitor
     * @param string                     $url     Extref URL
     * @param array                      $type    Type
     * @param Context                    $context Context
     * @return ExtReference|null
     */
    public function deserializeExtReferenceFromJson(
        JsonDeserializationVisitor $visitor,
        $url,
        array $type,
        Context $context
    ) {
        try {
            return $this->converter->getExtReference(
                $visitor->visitString($url, $type, $context)
            );
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }
}
