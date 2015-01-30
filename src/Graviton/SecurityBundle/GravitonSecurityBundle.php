<?php

namespace Graviton\SecurityBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\SecurityBundle\DependencyInjection\AuthenticationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonSecurityBundle
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class GravitonSecurityBundle extends Bundle implements GravitonBundleInterface
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
     * Find authentication strategies tagged as 'graviton.security.authentication.strategy'
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AuthenticationPass());
    }
}
