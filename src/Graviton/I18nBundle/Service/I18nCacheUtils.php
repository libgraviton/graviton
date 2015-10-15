<?php
/**
 * service for i18n stuff
 */

namespace Graviton\I18nBundle\Service;

use Doctrine\Common\Cache\FilesystemCache;

/**
 * A service (meaning symfony service) providing some convenience stuff when dealing with our RestController
 * based services (meaning rest services).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class I18nCacheUtils
{

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $loaderId;

    /**
     * @var string
     */
    private $resourceDir;

    /**
     * @var FilesystemCache
     */
    private $cache;

    private $addedResources = array();

    private $isDirty = false;

    /**
     * Constructor
     *
     * @param string              $defaultLanguage    default language
     */
    public function __construct(
        $cacheDir,
        $loaderId
    ) {
        // caching
        $this->cacheFile = $cacheDir . '/addedI18nResources';
        $this->cache = new FilesystemCache($this->cacheFile);

        // cache keys
        $this->cacheKey = 'addedTranslations';
        $this->cacheKeyFinalResource = 'finalResources';
        $this->cacheKeyFinalResourceHash = 'finalResourceHash';

        $this->loaderId = $loaderId;
        $this->resourceDir = __DIR__.'/../Resources/translations/';

        // do we have existing resources?
        if ($this->cache->contains($this->cacheKey)) {
            $this->addedResources = $this->cache->fetch($this->cacheKey);
        }
    }

    public function invalidate($locale, $domain)
    {
        $filename = sprintf('%s.%s.%s', $domain, $locale, $this->loaderId);

        if (!isset($this->addedResources[$locale]) || !in_array($filename, $this->addedResources[$locale])) {
            $this->addedResources[$locale][] = $filename;
            $this->isDirty = true;
        }
    }

    public function setResourceDir($dir)
    {
        $this->resourceDir = $dir;
    }

    public function getResources($resources)
    {
        $resourceHash = sha1(serialize($resources));

        $finalResources = null;
        if ($this->resourceIsInCache($resourceHash)) {
            $finalResources = $this->cache->fetch(cacheKeyResource);
        }

        if (!is_array($finalResources) && $this->cache->contains($this->cacheKey)) {
            // merge the two together, always keep an eye to not duplicate (paths are different!)
            $finalResources = $this->mergeResourcesWithAdditions($resources);

            //echo "hans"; die;
        }

        /*
        var_dump($resourceHash);
        var_dump($resources);


        $added = array();
        if ($this->cache->contains($this->cacheKey)) {
            $added = $this->cache->fetch($this->cacheKey);
        }

        var_dump($added); die;
        */

        return $resources;
    }

    /**
     * Merges the cached additions with the one the Translator already has.
     * I need to use preg_grep() here as I'm unable to compose an absolute path that is
     * exactly like the one the Translator would have already as I have to deal with relative
     * paths here (rightfully so). This shouldn't hurt too much as the end result is cache too
     * and only redone if something changes.
     *
     * @param $resources
     */
    private function mergeResourcesWithAdditions($resources)
    {
        //var_dump($resources);
        $this->addedResources['en'][] = 'core.en.odm';
        foreach ($this->addedResources as $locale => $files) {
            //var_dump($resources[$locale]); die;
            foreach ($files as $file) {
                //var_dump('/('.str_replace('.', '\\.', $file).')$/');
                $hits =preg_grep('/$'.str_replace('.', '\\.', $file).'/', $resources[$locale]);
                //var_dump($hits);
            }
        }

        //die;
    }

    private function resourceIsInCache($resourcesHash)
    {
        $ret = false;

        if ($this->cache->contains($this->cacheKeyFinalResourceHash) &&
            $this->cache->fetch($this->cacheKeyFinalResourceHash) == $resourcesHash
        ) {
            $ret = true;
        }

        return $ret;
    }

    private function persistAdditions()
    {
        $this->cache->save($this->cacheKey, $this->addedResources);

        // invalidate full map hash -> forces regeneration of the whole thing
        $this->cache->delete($this->cacheKeyFinalResourceHash);
    }

    public function __destruct()
    {
        if ($this->isDirty === true) {
            $this->persistAdditions();
        }
    }
}
