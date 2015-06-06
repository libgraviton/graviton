<?php
/**
 * abstract compiler pass for extref things
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractExtRefCompilerPass implements CompilerPassInterface
{
    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    final public function process(ContainerBuilder $container)
    {
        $gravitonServices = $container->findTaggedServiceIds(
            'graviton.rest'
        );
        $this->processServices($container, array_keys($gravitonServices));
    }

    /**
     * abstract process method
     *
     * @param ContainerBuilder $container container
     * @param array            $services  services
     *
     * @return void
     */
    abstract public function processServices(ContainerBuilder $container, $services);
}
