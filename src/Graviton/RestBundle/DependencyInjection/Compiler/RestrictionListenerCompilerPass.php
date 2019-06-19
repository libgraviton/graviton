<?php
/**
 * remove restrictionListener if not needed..
 */

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionListenerCompilerPass implements CompilerPassInterface
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
        $param = $container->getParameter('graviton.rest.data_restriction.map');
        if (!is_array($param) || empty($param)) {
            // remove listener
            $container->removeDefinition('graviton.rest.listener.restriction');
        }
    }
}
