<?php
/**
 * security bundle
 */

namespace Graviton\SecurityBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\SecurityBundle\DependencyInjection\AuthenticationPass;
use Graviton\SecurityBundle\DependencyInjection\Compiler\AuthenticationKeyFinderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonSecurityBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
     * {@inheritDoc}
     *
     * @param ContainerBuilder $container container to add compiler-pass to
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AuthenticationKeyFinderPass());
    }
}
