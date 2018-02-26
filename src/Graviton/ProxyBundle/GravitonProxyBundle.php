<?php
/**
 * Provide a Proxy for third party APIs.
 */

namespace Graviton\ProxyBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\ProxyBundle\DependencyInjection\Compiler\ApiDefinitionLoaderPass;
use Graviton\ProxyBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonProxyBundle
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class GravitonProxyBundle extends Bundle implements GravitonBundleInterface
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

    /**
     * {@inheritDoc}
     *
     * @param ContainerBuilder $container Symfony Service container
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TransformerPass());
        $container->addCompilerPass(new ApiDefinitionLoaderPass());
    }
}
