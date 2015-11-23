<?php
/**
 * TimeCacheStrategy
 */

namespace Graviton\ProxyBundle\Definition\Loader\CacheStrategy;

use Gaufrette\Exception\FileNotFound;
use Gaufrette\Filesystem;

/**
 * cache content with a defined length of time
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TimeCacheStrategy implements CacheStrategyInterface
{
    /**
     * @var Filesystem
     */
    private $gaufrette;

    /**
     * @var int
     */
    private $cacheTime;

    /**
     * constructor
     *
     * @param Filesystem $gaufrette     gaufrette filesystem
     * @param string     $cacheDuration how long is the cached file valid
     */
    public function __construct(Filesystem $gaufrette, $cacheDuration)
    {
        $this->gaufrette = $gaufrette;
        $this->cacheTime = (int) $cacheDuration;
    }

    /**
     * return the cached content
     *
     * @param string $key key
     *
     * @return string content of the cached file
     */
    public function get($key)
    {
        try {
            $content = $this->gaufrette->read($key);
        } catch (FileNotFound $e) {
            $content = '';
        }

        return $content;
    }

    /**
     * cache content
     *
     * @param string $key     key
     * @param string $content content to cache
     *
     * @return void
     */
    public function save($key, $content)
    {
        $this->gaufrette->write($key, $content, true);
    }

    /**
     * check whether cached content is expired
     *
     * @param string $key key
     *
     * @return bool
     */
    public function isExpired($key)
    {
        $retVal = true;
        if ($this->gaufrette->has($key)) {
            $mTime = $this->gaufrette->mtime($key);
            if (($mTime + $this->cacheTime) >= time()) {
                $retVal = false;
            }
        }

        return $retVal;
    }
}
