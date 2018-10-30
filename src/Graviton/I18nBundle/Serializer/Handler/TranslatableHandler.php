<?php
/**
 * TranslatableHandler class file
 */

namespace Graviton\I18nBundle\Serializer\Handler;

use Graviton\DocumentBundle\Entity\Translatable;
use Graviton\I18nBundle\Service\I18nUtils;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

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
     * @var I18nUtils
     */
    private $utils;

    /**
     * Constructor
     *
     * @param I18nUtils $utils i18nutils
     */
    public function __construct(I18nUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Serialize Translatable to JSON
     *
     * @param JsonSerializationVisitor $visitor      Visitor
     * @param Translatable             $translatable translatable
     * @param array                    $type         Type
     * @param Context                  $context      Context
     * @return string|null
     */
    public function serializeTranslatableToJson(
        JsonSerializationVisitor $visitor,
        $translatable,
        array $type,
        Context $context
    ) {
        if (is_array($translatable) && empty($translatable)) {
            return $translatable;
        }

        $translations = $translatable->getTranslations();
        $defaultLanguage = $this->utils->getDefaultLanguage();
        if (isset($translations[$defaultLanguage]) && count($translations) != $this->utils->getLanguages()) {
            // languages missing
            $original = $translations[$defaultLanguage];
            $translated = $this->utils->getTranslatedField($original);

            $translatable->setTranslations(
                array_merge(
                    $translated,
                    $translatable->getTranslations()
                )
            );
        }

        return $translatable;
    }

    /**
     * Serialize Translatable from JSON
     *
     * @param JsonDeserializationVisitor $visitor Visitor
     * @param array                      $data    translation array as we represent if to users
     * @param array                      $type    Type
     * @param Context                    $context Context
     *
     * @return Translatable|null
     */
    public function deserializeTranslatableFromJson(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        if (!is_null($data)) {
            $translatable = Translatable::createFromTranslations($data);
            $this->utils->persistTranslatable($translatable);
            return $translatable;
        }
        return null;
    }
}
