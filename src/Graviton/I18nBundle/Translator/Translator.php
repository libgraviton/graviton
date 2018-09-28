<?php
/**
 * a simple translator
 */

namespace Graviton\I18nBundle\Translator;

use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\DocumentBundle\Entity\Translatable;
use Graviton\I18nBundle\Document\Language;
use Graviton\I18nBundle\Document\Translation;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Translator
{

    /**
     * @var Collection
     */
    private $translationCollection;

    /**
     * @var Collection
     */
    private $languageCollection;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @var array language ids
     */
    private $languages = [];

    /**
     * Translator constructor.
     *
     * @param DocumentManager $manager         manager
     * @param string          $defaultLanguage default language
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function __construct(DocumentManager $manager, $defaultLanguage)
    {
        $this->translationCollection = $manager->getDocumentCollection(Translation::class);
        $this->languageCollection = $manager->getDocumentCollection(Language::class);
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * translate a string, returning an array with translations
     *
     * @param string $original original string
     *
     * @return array translation array
     */
    public function translate($original)
    {
        $translations = $this->translationCollection->find(['original' => $original])->toArray();
        $baseArray = [];

        foreach ($translations as $item) {
            $baseArray[$item['language']] = $item['localized'];
        }

        $translation = [];
        foreach ($this->getLanguages() as $language) {
            if (isset($baseArray[$language])) {
                $translation[$language] = $baseArray[$language];
            } else {
                $translation[$language] = $original;
            }
        }

        // ensure existence of default language
        $translation[$this->defaultLanguage] = $original;

        return $translation;
    }

    /**
     * get DefaultLanguage
     *
     * @return string DefaultLanguage
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * persists a translatable to database
     *
     * @param Translatable $translatable the translatable
     *
     * @return void
     */
    public function persistTranslatable(Translatable $translatable)
    {
        $translations = $translatable->getTranslations();
        if (!isset($translations[$this->defaultLanguage])) {
            throw new \LogicException('Not possible to persist a Translatable without default language!');
        }

        $original = $translations[$this->defaultLanguage];
        unset($translations[$this->defaultLanguage]);

        foreach ($translations as $language => $translation) {
            $this->translationCollection->upsert(
                [
                    'original' => $original,
                    'language' => $language
                ],
                [
                    'original' => $original,
                    'language' => $language,
                    'localized' => $translation
                ]
            );
        }
    }

    /**
     * returns all available languages
     *
     * @return array languages
     */
    public function getLanguages()
    {
        if (!empty($this->languages)) {
            return $this->languages;
        }

        $this->languages = array_map(
            function ($record) {
                return $record['_id'];
            },
            $this->languageCollection->find([], ['_id' => 1])->toArray()
        );

        $this->languages = array_values($this->languages);

        return $this->languages;
    }
}
