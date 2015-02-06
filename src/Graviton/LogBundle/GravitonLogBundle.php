<?php

namespace Graviton\LogBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonLogBundle
 *
 * @category GravitonLogBundle
 * @package  Graviton
 * @link     http://swisscom.ch
 */
class GravitonLogBundle extends Bundle implements GravitonBundleInterface
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
