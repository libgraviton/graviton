<?php

namespace Graviton\CacheBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonCacheBundle
 *
 * @category GravitonCacheBundle
 * @package  Graviton
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
