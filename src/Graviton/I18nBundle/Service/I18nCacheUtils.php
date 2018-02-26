<?php
/**
 * service for i18n stuff relating to symfony translation loader and its caching.
 *
 * it basically receives the 'resource_files' array from a Translator (that is built statically in the Container)
 * and adds runtime elements to it. behind the scenes, it invalidates translator caches and creates the resource files.
 * all with the idea that new 'translation domains' get added at runtime without the need of a container rebuild.
 * only then it will be actually loaded by the translation loaders and end up in the final catalogue.
 * caches are in place to optimize the performance impact.
 */

namespace Graviton\I18nBundle\Service;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class I18nCacheUtils
{

    /**
     * doctrine cache
     *
     * @var CacheProvider
     */
    private $cache;

    /**
     * full path to translations cache
     *
     * @var string
     */
    private $cacheDirTranslations;

    /**
     * the cache key we use for our addition array
     *
     * @var string
     */
    private $cacheKey;

    /**
     * the cache key we use for the full resource map
     *
     * @var string
     */
    private $cacheKeyFinalResource;

    /**
     * the loader id suffix (like 'odm')
     *
     * @var string
     */
    private $loaderId;

    /**
     * full path to the bundle resource dir
     *
     * @var string
     */
    private $resourceDir;

    /**
     * this map contains all added resources (added to all translator knows already)
     *
     * @var array
     */
    private $addedResources = array();

    /**
     * if the postpersistListener invalidates something, it will be put here
     *
     * @var array
     */
    private $invalidations = array();

    /**
     * a boolean flag telling us if a new map persist is necessary
     * (that is, when some new addition has been added that needs a resource map regeneration)
     *
     * @var boolean
     */
    private $isDirty = false;

    /**
     * Constructor
     *
     * @param CacheProvider $cache    cache
     * @param string        $cacheDir full path to cache dir
     * @param string        $loaderId loader id suffix
     */
    public function __construct(
        CacheProvider $cache,
        $cacheDir,
        $loaderId
    ) {
        $this->cache = $cache;
        $this->cacheDirTranslations = $cacheDir . '/translations';

        // cache keys
        $this->cacheKey = 'i18n.addedTranslations';
        $this->cacheKeyFinalResource = 'i18n.finalResources';

        $this->loaderId = $loaderId;
        $this->resourceDir = __DIR__.'/../Resources/translations/';

        // do we have existing resources?
        if ($this->cache->contains($this->cacheKey)) {
            $this->addedResources = $this->cache->fetch($this->cacheKey);
        }
    }

    /**
     * Gets the cache instance
     *
     * @return CacheProvider cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * sets the resource dir
     *
     * @param string $dir resource dir
     *
     * @return void
     */
    public function setResourceDir($dir)
    {
        $this->resourceDir = $dir;
    }

    /**
     * this shall be called by a Translator.
     * it adds our additions to the already existent ones in the Translator and returns it.
     * as this is called quite often, we cache the final result (the full map including the translator resources)
     * and only regenerate that when a *new* domain has been added. basic invalidations by the PostPersistListener
     * will *not* result in a rebuild here - only if a new domain has been added.
     *
     * @param array $resources the resources array of the translator
     *
     * @return array the finalized map containing translator resources and our additions
     */
    public function getResources($resources)
    {
        $finalResources = null;

        // do we have a full resource map in the cache already? (full = translator + additions)
        if ($this->cache->contains($this->cacheKeyFinalResource)) {
            $finalResources = $this->cache->fetch($this->cacheKeyFinalResource);
        }

        // do we have cached additions?
        if (!is_array($finalResources) && $this->cache->contains($this->cacheKey)) {
            // merge the two together, always keep an eye to not duplicate (paths are different!)
            $finalResources = $this->mergeResourcesWithAdditions($resources);

            // cache it
            $this->cache->save($this->cacheKeyFinalResource, $finalResources);
        }

        // so, did we got anything?
        if (is_array($finalResources)) {
            $resources = $finalResources;
        }

        return $resources;
    }

    /**
     * will be executed on the event dispatched by PostPersistTranslatableListener.
     * if someone invalidates a locale & domain pair, this will lead to:
     * - removal of the symfony translation cache files
     * (if this pair has never been seen)
     * - creation of the resource files ("trigger files")
     * - a regeneration of the full resource map for the translator
     *
     * please note that calling invalidate() will do the above mentioned in a lazy way
     * when the kernel.terminate event fires.
     *
     * @param string $locale locale (de,en,fr)
     * @param string $domain domain
     *
     * @return void
     */
    public function invalidate($locale, $domain)
    {
        $filename = sprintf('%s.%s.%s', $domain, $locale, $this->loaderId);

        if (!isset($this->addedResources[$locale]) || !in_array($filename, $this->addedResources[$locale])) {
            $this->addedResources[$locale][] = $filename;
            $this->isDirty = true;
        }

        $this->invalidations[$locale] = '';
    }

    /**
     * Merges the cached additions with the one the Translator already has.
     * I need to use preg_grep() here as I'm unable to compose an absolute path that is
     * identical to the one the Translator would have already as I have to deal with relative
     * paths here (and rightfully so). This shouldn't hurt too much as the end result is cached
     * and only redone if something changes.
     *
     * @param array $resources resources
     *
     * @return array finalized full map
     */
    private function mergeResourcesWithAdditions($resources)
    {
        foreach ($this->addedResources as $locale => $files) {
            foreach ($files as $file) {
                $isExistent = false;
                if (isset($resources[$locale])) {
                    $hits = preg_grep('/\/'.str_replace('.', '\\.', $file).'$/', $resources[$locale]);
                    if (count($hits) > 0) {
                        $isExistent = true;
                    }
                }

                if (!$isExistent) {
                    $resourceFile = $this->resourceDir.$file;

                    // make sure the file exists
                    $fs = new Filesystem();
                    $fs->touch($resourceFile);

                    $resources[$locale][] = $resourceFile;
                }
            }
        }
        return $resources;
    }

    /**
     * saves our addition array to our cache and removes the full map from the cache
     * leading to a regeneration of the map.
     *
     * @return void
     */
    private function persistAdditions()
    {
        $this->cache->save($this->cacheKey, $this->addedResources);

        // remove full map from cache
        $this->cache->delete($this->cacheKeyFinalResource);
    }

    /**
     * processes all queued cache invalidations for the symfony translation cache.
     * this is now only 1 Finder search for a single request.
     *
     * @return void
     */
    private function processInvalidations()
    {
        if (empty($this->invalidations)) {
            return;
        }

        $fs = new Filesystem();
        $localesToClean = array_keys($this->invalidations);
        $deleteRegex = '/^catalogue\.(['.implode('|', $localesToClean).'])/';

        try {
            $finder = new Finder();
            $finder
                ->files()
                ->in($this->cacheDirTranslations)
                ->name($deleteRegex);

            foreach ($finder as $file) {
                $fs->remove($file->getRealPath());
            }
        } catch (\InvalidArgumentException $e) {
            // happens when cache is non-existent
        }
    }

    /**
     * processes all pending operations
     *
     * @return void
     */
    public function processPending()
    {
        $this->processInvalidations();

        if ($this->isDirty === true) {
            $this->persistAdditions();
        }
    }
}
