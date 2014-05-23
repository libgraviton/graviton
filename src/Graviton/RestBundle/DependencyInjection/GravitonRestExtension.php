<?php

namespace Graviton\RestBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GravitonRestExtension extends GravitonBundleExtension
{
    /**
     * {@inheritDoc}
     *
     * @return String
     */
    public function getConfigDir()
    {
        return __DIR__.'/../Resources/config';
    }
}
