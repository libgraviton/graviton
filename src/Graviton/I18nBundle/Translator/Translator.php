<?php
/**
 * a simple translator
 */

namespace Graviton\I18nBundle\Translator;

use Doctrine\Common\Cache\CacheProvider;
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
     * @var string
     */
    const CACHE_KEY_LANGUAGES = 'translator_languages';

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
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var CacheProvider
     */
    private $cacheRewrite;

    /**
     * @var int
     */
    private $cacheNameDepth;

    /**
     * @var array language ids
     */
    private $languages = [];

    /**
     * Translator constructor.
     *
     * @param DocumentManager $manager         manager
     * @param string          $defaultLanguage default language
     * @param CacheProvider   $cache           cache adapter
     * @param CacheProvider   $cacheRewrite    cache adapter for rewrites
     * @param int             $cacheNameDepth  how many characters of the original is used as cache pool divider
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function __construct(
        DocumentManager $manager,
        $defaultLanguage,
        CacheProvider $cache,
        CacheProvider $cacheRewrite,
        $cacheNameDepth
    ) {
        $this->translationCollection = $manager->getDocumentCollection(Translation::class);
        $this->languageCollection = $manager->getDocumentCollection(Language::class);
        $this->defaultLanguage = $defaultLanguage;
        $this->cache = $cache;
        $this->cacheRewrite = $cacheRewrite;
        $this->cacheNameDepth = (int) $cacheNameDepth;
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
        $cached = $this->getFromCache($original);
        if (is_array($cached)) {
            return $cached;
        }

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

        $this->saveToCache($original, $translation);

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

        $this->removeFromCache($original);
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

        $this->languages = $this->cacheRewrite->fetch(self::CACHE_KEY_LANGUAGES);

        if ($this->languages === false) {
            $this->languages = array_map(
                function ($record) {
                    return $record['_id'];
                },
                $this->languageCollection->find([], ['_id' => 1])->toArray()
            );

            asort($this->languages);

            $this->languages = array_values($this->languages);

            $this->cacheRewrite->save(self::CACHE_KEY_LANGUAGES, $this->languages);
        }

        return $this->languages;
    }

    /**
     * removes the cached languages
     *
     * @return void
     */
    public function removeCachedLanguages()
    {
        $this->languages = [];
        $this->cacheRewrite->delete(self::CACHE_KEY_LANGUAGES);
    }

    /**
     * gets entry from cache
     *
     * @param string $original original string
     *
     * @return array|null entry
     */
    private function getFromCache($original)
    {
        $cacheContent = $this->cache->fetch($this->getCacheKey($original));
        if (is_array($cacheContent) && isset($cacheContent[$original])) {
            return $cacheContent[$original];
        }

        return null;
    }

    /**
     * saves entry to cache
     *
     * @param string $original     original string
     * @param array  $translations translations
     *
     * @return void
     */
    private function saveToCache($original, $translations)
    {
        $cacheKey = $this->getCacheKey($original);
        $cacheContent = $this->cache->fetch($cacheKey);
        if (is_array($cacheContent)) {
            $cacheContent[$original] = $translations;
        } else {
            $cacheContent = [$original => $translations];
        }

        $this->cache->save($cacheKey, $cacheContent);
    }

    /**
     * removes entry from cache
     *
     * @param string $original original string
     *
     * @return void
     */
    private function removeFromCache($original)
    {
        $cacheKey = $this->getCacheKey($original);
        $cacheContent = $this->cache->fetch($this->getCacheKey($original));
        if (is_array($cacheContent) && isset($cacheContent[$original])) {
            unset($cacheContent[$original]);
            $this->cache->save($cacheKey, $cacheContent);
        }
    }

    /**
     * returns the caching key
     *
     * @param string $original original string
     *
     * @return string cache key
     */
    private function getCacheKey($original)
    {
        return sprintf(
            'translator_%s_%s',
            implode('.', $this->getLanguages()),
            str_pad(strtolower(substr($original, 0, $this->cacheNameDepth)), $this->cacheNameDepth, '.')
        );
    }
}
