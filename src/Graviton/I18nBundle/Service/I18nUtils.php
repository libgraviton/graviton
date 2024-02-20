<?php
/**
 * service for i18n stuff
 */

namespace Graviton\I18nBundle\Service;

/**
 * simple getters for static stuff
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class I18nUtils
{

    /**
     * @var string
     */
    private string $defaultLanguage;

    /**
     * @var string
     */
    private string $languages;

    /**
     * Constructor
     *
     * @param string $defaultLanguage default lang
     * @param string $languages       langs
     */
    public function __construct(
        string $defaultLanguage,
        string $languages
    ) {
        $this->defaultLanguage = $defaultLanguage;
        $this->languages = $languages;
    }

    /**
     * Returns the default/original language
     *
     * @return string default language
     */
    public function getDefaultLanguage()
    {
        return strtolower(trim($this->defaultLanguage));
    }

    /**
     * Returns all languages as a simple flat array
     *
     * @return array array of language id's
     */
    public function getLanguages() : array
    {
        return array_map(
            function ($lang) {
                return trim(strtolower($lang));
            },
            explode(',', $this->languages)
        );
    }
}
