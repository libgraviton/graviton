<?php
/**
 * handle unit and functional testing
 */

namespace Graviton\TestBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonTestBundle
 *
 * @category GravitonTestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonTestBundle extends Bundle implements GravitonBundleInterface
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
