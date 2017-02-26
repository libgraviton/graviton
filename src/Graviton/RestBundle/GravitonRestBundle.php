<?php
/**
 * GravitonRestBundle
 */

namespace Graviton\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Graviton\BundleBundle\GravitonBundleInterface;
use JMS\SerializerBundle\JMSSerializerBundle;
use Misd\GuzzleBundle\MisdGuzzleBundle;
use Graviton\RestBundle\DependencyInjection\Compiler\RestServicesCompilerPass;
use Graviton\RestBundle\DependencyInjection\Compiler\RqlQueryDecoratorCompilerPass;
use Graviton\RestBundle\DependencyInjection\Compiler\RqlQueryRoutesCompilerPass;

/**
 * GravitonRestBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
            new MisdGuzzleBundle(),
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
    }
}
