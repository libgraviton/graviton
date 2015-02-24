<?php
/**
 * bundle containing various code generators
 */

namespace Graviton\GeneratorBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * bundle containing various code generators
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonGeneratorBundle extends Bundle implements GravitonBundleInterface
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
