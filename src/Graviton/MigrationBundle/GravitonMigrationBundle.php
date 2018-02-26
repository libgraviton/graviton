<?php
/**
 * core infrastructure like logging and framework.
 */

namespace Graviton\MigrationBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonMigrationBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonMigrationBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * set up graviton symfony bundles
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return [];
    }
}
