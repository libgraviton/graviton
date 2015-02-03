<?php

namespace Graviton\GeneratorBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * bundle containing various code generators
 *
 * @category GeneratorBundle
 * @package  Graviton
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
