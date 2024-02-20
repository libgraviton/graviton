<?php
/**
 * TranslatableHandler class file
 */

namespace Graviton\I18nBundle\Serializer\Handler;

use Graviton\DocumentBundle\Entity\Translatable;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * JMS serializer handler for Translatable
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatableHandler
{

    /**
     * Serialize Translatable to JSON
     *
     * @param SerializationVisitorInterface $visitor      Visitor
     * @param Translatable                  $translatable translatable
     * @param array                         $type         Type
     * @param Context                       $context      Context
     * @return string|null
     */
    public function serializeTranslatableToJson(
        SerializationVisitorInterface $visitor,
        $translatable,
        array $type,
        Context $context
    ) {
        if (is_array($translatable) && empty($translatable)) {
            return $translatable;
        }

        if ($translatable instanceof Translatable) {
            return $translatable->getTranslations();
        }
    }

    /**
     * Serialize Translatable from JSON
     *
     * @param DeserializationVisitorInterface $visitor Visitor
     * @param array                           $data    translation array as we represent if to users
     * @param array                           $type    Type
     * @param Context                         $context Context
     *
     * @return Translatable|null
     */
    public function deserializeTranslatableFromJson(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        Context $context
    ) {
        if (!is_null($data)) {
            return Translatable::createFromTranslations((array) $data);
        }
        return null;
    }
}
