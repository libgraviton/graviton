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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
