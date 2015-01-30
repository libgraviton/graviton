<?php
/**
 * Interface for autoregistrable bundles.
 */

namespace Graviton\BundleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonBundleInterface
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
interface GravitonBundleInterface
{
    /**
     * return array of new bunde instances
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles();
}
