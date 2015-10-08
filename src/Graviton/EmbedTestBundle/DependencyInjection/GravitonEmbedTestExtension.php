<?php
namespace Graviton\EmbedTestBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;

class GravitonEmbedTestExtension extends GravitonBundleExtension
{
    public function getConfigDir()
    {
        return __DIR__.'/../Resources/config';
    }
}
