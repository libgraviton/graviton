<?php
/**
 * find all services tagged as 'graviton.rest' and store them in 'graviton.rest.services'
 */

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RestServicesCompilerPass implements CompilerPassInterface
{
    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter(
            'graviton.rest.services',
            $container->findTaggedServiceIds('graviton.rest')
        );
    }
}
