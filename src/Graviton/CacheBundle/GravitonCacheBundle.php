<?php
/**
 * GravitonCacheBundle
 */

namespace Graviton\CacheBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonCacheBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * return array of new bunde instances
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array();
    }
}
