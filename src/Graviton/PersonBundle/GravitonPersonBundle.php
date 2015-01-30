<?php

namespace Graviton\PersonBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonPersonBundle
 *
 * @category PersonBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonPersonBundle extends Bundle implements GravitonBundleInterface
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
