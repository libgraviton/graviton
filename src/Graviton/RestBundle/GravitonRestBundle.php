<?php
/**
 * GravitonRestBundle
 */

namespace Graviton\RestBundle;

use Graviton\RestBundle\DependencyInjection\Compiler\RestrictionListenerCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Graviton\BundleBundle\GravitonBundleInterface;
use JMS\SerializerBundle\JMSSerializerBundle;
use Graviton\RestBundle\DependencyInjection\Compiler\RestServicesCompilerPass;
use Graviton\RestBundle\DependencyInjection\Compiler\RqlQueryRoutesCompilerPass;

/**
 * GravitonRestBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonRestBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * set up basic bundles needed for being RESTful
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array(
            new JMSSerializerBundle(),
        );
    }

    /**
     * load compiler pass rest route loader
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RestServicesCompilerPass);
        $container->addCompilerPass(new RqlQueryRoutesCompilerPass());
        $container->addCompilerPass(new RestrictionListenerCompilerPass());
    }
}
