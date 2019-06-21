<?php
/**
 * Graviton internationalization plugin
 */

namespace Graviton\I18nBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * Graviton internationalization plugin
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
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
        return [];
    }

    /**
     * add our compiler pass
     *
     * @param ContainerBuilder $container container
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
