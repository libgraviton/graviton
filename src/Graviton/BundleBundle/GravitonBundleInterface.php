<?php
/**
 * Interface for autoregistrable bundles.
 */

namespace Graviton\BundleBundle;

/**
 * GravitonBundleInterface
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface GravitonBundleInterface
{
    /**
     * return array of new bunde instances
     *
     * @return Array
     */
    public function getBundles();
}
