<?php
/**
 * Generate swagger conform specs.
 */

namespace Graviton\SwaggerBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\SwaggerBundle\DependencyInjection\Compiler\SwaggerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonSwaggerBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonSwaggerBundle extends Bundle implements GravitonBundleInterface
{

    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return [];
    }

    /**
     * load compiler pass
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SwaggerCompilerPass());
    }
}
