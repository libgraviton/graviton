<?php
/**
 * bundle for autoregistering bundles in graviton
 */

namespace Graviton\BundleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonBundleBundle
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonBundleBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * @return Array
     */
    public function getBundles()
    {
        return array();
    }
}
