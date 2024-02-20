<?php
/**
 * Translatable class file
 */

namespace Graviton\DocumentBundle\Entity;

/**
 * entity to represent a translatable
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Translatable implements \JsonSerializable
{

    /**
     * @var array translated array
     */
    private $translations = [];

    private static $defaultLanguage;

    /**
     * creates a translatable from a simple string
     *
     * @param string $original original text
     *
     * @return Translatable translatable
     */
    public static function createFromOriginalString($original)
    {
        return (new Translatable())->setTranslations(
            [
                self::getDefaultLanguage() => $original
            ]
        );
    }

    /**
     * creates a translatable from an existing array of translations
     *
     * @param array $translations translations
     *
     * @return Translatable translatable
     */
    public static function createFromTranslations(array $translations)
    {
        return (new Translatable())->setTranslations($translations);
    }

    /**
     * returns the default language as statically generated in a file
     *
     * @return string default language
     */
    public static function getDefaultLanguage()
    {
        if (!is_null(self::$defaultLanguage)) {
            return self::$defaultLanguage;
        }

        if (!file_exists(self::$defaultLanguageFile)) {
            throw new \LogicException('Default language file '.self::$defaultLanguageFile.' does not exist');
        }

        self::$defaultLanguage = file_get_contents(self::$defaultLanguageFile);

        if (empty(self::$defaultLanguage)) {
            unlink(self::$defaultLanguageFile);
            throw new \LogicException('Default language file is empty!');
        }

        return self::$defaultLanguage;
    }

    /**
     * get Translations
     *
     * @return array Translations
     */
    public function getTranslations()
    {
        if (!empty($this->translations)) {
            return $this->translations;
        }

        return [];
    }

    /**
     * set Translations
     *
     * @param array $translations translations
     *
     * @return Translatable
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }

    /**
     * if this one has translations or not
     *
     * @return bool true if yes, false otherwise
     */
    public function hasTranslations()
    {
        return !empty($this->translations);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize() : mixed
    {
        if (!$this->hasTranslations()) {
            return null;
        }

        return $this->translations;
    }
}
