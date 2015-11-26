<?php
/**
 * CacheStrategyInterface
 */

namespace Graviton\ProxyBundle\Definition\Loader\CacheStrategy;

/**
 * cache strategy interface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface CacheStrategyInterface
{
    /**
     * return the cached content
     *
     * @param string $key key
     *
     * @return string content of the cached file
     */
    public function get($key);

    /**
     * cache content
     *
     * @param string $key     key
     * @param string $content content to cache
     *
     * @return void
     */
    public function save($key, $content);

    /**
     * check whether cached content is expired
     *
     * @param string $key key
     *
     * @return bool
     */
    public function isExpired($key);
}
