<?php

namespace Graviton\I18nBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * Graviton internationalization plugin
 *
 * @category I18nBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonI18nBundle extends Bundle implements GravitonBundleInterface
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
        return array();
    }
}
