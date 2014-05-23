<?php

namespace Graviton\SchemaBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GravitonSchemaExtension extends GravitonBundleExtension
{
    public function getConfigDir()
    {
        return __DIR__.'/../Resources/config';
    }
}
