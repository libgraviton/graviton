<?php
/**
 * a simple translator
 */

namespace Graviton\I18nBundle\Translator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\DocumentBundle\Entity\Translatable;
use Graviton\I18nBundle\Document\Language;
use Graviton\I18nBundle\Document\Translation;
use MongoDB\Collection;
use Psr\Cache\CacheItemPoolInterface;

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
     * @var CacheItemPoolInterface
     */
    private $cache;

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
     * @param DocumentManager        $manager         manager
     * @param string                 $defaultLanguage default language
     * @param CacheItemPoolInterface $cache           cache adapter
     * @param int                    $cacheNameDepth  how many characters of the original is used as cache pool divider
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function __construct(
        DocumentManager $manager,
        $defaultLanguage,
        CacheItemPoolInterface $cache,
        $cacheNameDepth
    ) {
        $this->translationCollection = $manager->getDocumentCollection(Translation::class);
        $this->languageCollection = $manager->getDocumentCollection(Language::class);
        $this->defaultLanguage = $defaultLanguage;
        $this->cache = $cache;
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
            $this->translationCollection->updateOne(
                [
                    'original' => $original,
                    'language' => $language
                ],
                [
                    '$set' => [
                        'original' => $original,
                        'language' => $language,
                        'localized' => $translation
                    ]
                ],
                [
                    'upsert' => true
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

        $cacheItem = $this->cache->getItem(self::CACHE_KEY_LANGUAGES);
        $this->languages = $cacheItem->get();

        if (!is_array($this->languages)) {
            $this->languages = array_map(
                function ($record) {
                    return $record['_id'];
                },
                $this->languageCollection->find([], ['_id' => 1])->toArray()
            );

            asort($this->languages);

            $this->languages = array_values($this->languages);

            $cacheItem->set($this->languages);
            $this->cache->save($cacheItem);
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
        $this->cache->deleteItem(self::CACHE_KEY_LANGUAGES);
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
        $cacheItem = $this->cache->getItem($this->getCacheKey($original));
        if (!$cacheItem->isHit()) {
            return null;
        }

        $cacheContent = $cacheItem->get();
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
        $cacheItem = $this->cache->getItem($cacheKey);

        $cacheContent = $cacheItem->get();
        if (is_array($cacheContent)) {
            $cacheContent[$original] = $translations;
        } else {
            $cacheContent = [$original => $translations];
        }

        $cacheItem->set($cacheContent);
        $this->cache->save($cacheItem);
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
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            return;
        }

        $cacheContent = $cacheItem->get();
        if (is_array($cacheContent) && isset($cacheContent[$original])) {
            unset($cacheContent[$original]);

            $cacheItem->set($cacheContent);
            $this->cache->save($cacheItem);
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
            sha1($original)
        );
    }
}
