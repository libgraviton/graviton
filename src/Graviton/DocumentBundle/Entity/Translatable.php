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
     * @var string the original string
     */
    private $original;

    /**
     * @var array translated array
     */
    private $translations = [];

    /**
     * creates a translatable from a simple string
     *
     * @param string $original original text
     *
     * @return Translatable translatable
     */
    public static function createFromOriginalString($original)
    {
        return (new Translatable())->setOriginal($original);
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
     * get Original
     *
     * @return mixed Original
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * set Original
     *
     * @param mixed $original original
     *
     * @return Translatable
     */
    public function setOriginal($original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     * get Translations
     *
     * @return array Translations
     */
    public function getTranslations()
    {
        if (empty($this->translations) && isset($this->original)) {
            //return ['en' => $this->original];
        }

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
    public function jsonSerialize()
    {
        if (!$this->hasTranslations()) {
            return null;
        }

        return $this->translations;
    }
}
