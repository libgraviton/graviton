<?php
/**
 * Graviton ProxyApi Bundle
 */
namespace Graviton\ProxyApiBundle;

use Graviton\ProxyApiBundle\DependencyInjection\Compiler\ServicesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for ProxyApi
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonProxyApiBundle extends Bundle
{

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

        $container->addCompilerPass(new ServicesCompilerPass());
    }
}
