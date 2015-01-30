<?php

namespace Graviton\EntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * GravitonEntityBundle
 *
 * @category GravitonEntityBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonEntityBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array();
    }
}
