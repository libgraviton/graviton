<?php
/**
 * unit test for I18nCacheUtils
 */

namespace Graviton\I18nBundle\Tests\Service;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Graviton\I18nBundle\Service\I18nCacheUtils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class I18nCacheUtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * path to our local resources
     *
     * @var string
     */
    private $resourceDir;

    /**
     * cache
     *
     * @var CacheProvider
     */
    private $cache;

    /**
     * cache utils
     *
     * @var I18nCacheUtils
     */
    private $cacheUtils;

    /**
     * setup function
     *
     * @return void
     */
    public function setUp()
    {
        $this->resourceDir = __DIR__.'/resources/translations/';

        $this->cache = new ArrayCache();
        $this->cacheUtils = new I18nCacheUtils($this->cache, __DIR__.'/resources/cache', 'odm');
        $this->cacheUtils->setResourceDir($this->resourceDir);
    }

    /**
     * see if I18nCacheUtils does nothing when it has nothing
     *
     * @return void
     */
    public function testLeaveAloneIfNothing()
    {
        $resources = [
            'de' => ['/hans.po']
        ];

        $this->assertEquals($resources, $this->cacheUtils->getResources($resources));
    }

    /**
     * test the whole process
     *
     * @return void
     */
    public function testProcess()
    {
        $this->cacheUtils->invalidate('de', 'hans');
        $this->cacheUtils->invalidate('fr', 'franz');

        // we did invalidations.. resources files shall not be here yet! (test lazyness)
        $this->assertFileNotExists($this->resourceDir.'hans.de.odm');
        $this->assertFileNotExists($this->resourceDir.'franz.fr.odm');

        $this->cacheUtils->processPending();

        $resources = [
            'de' => ['/hans.po']
        ];

        $finalResources = $this->cacheUtils->getResources($resources);

        // see if our final resource is in the cache
        $this->assertEquals($finalResources, $this->cacheUtils->getCache()->fetch('i18n.finalResources'));

        // see if our files are in the arrays
        $this->assertEquals('/hans.po', $finalResources['de'][0]);
        $this->assertEquals($this->resourceDir.'hans.de.odm', $finalResources['de'][1]);
        $this->assertEquals($this->resourceDir.'franz.fr.odm', $finalResources['fr'][0]);

        // ok, now the files *need* to exist..
        $this->assertFileExists($this->resourceDir.'hans.de.odm');
        $this->assertFileExists($this->resourceDir.'franz.fr.odm');

        // now, invalidate something more
        $this->cacheUtils->invalidate('de', 'trudi');

        // process
        $this->cacheUtils->processPending();
        $finalResources = $this->cacheUtils->getResources($resources);

        // again put in cache?
        $this->assertEquals($finalResources, $this->cacheUtils->getCache()->fetch('i18n.finalResources'));

        // file exists?
        $this->assertFileExists($this->resourceDir.'trudi.de.odm');

        // new item in map?
        $this->assertEquals($this->resourceDir.'trudi.de.odm', $finalResources['de'][2]);
    }

    /**
     * clean up our mess
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        // remove resources
        $fs = new Filesystem();
        $finder = new Finder();
        $finder
            ->files()
            ->ignoreDotFiles(true)
            ->in(__DIR__ . '/resources/translations');

        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }
    }
}
