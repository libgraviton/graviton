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
abstract class ExtRefCompilerPass implements CompilerPassInterface
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
        $map = [];
        $gravitonServices = array_filter(
            $container->getServiceIds(),
            function ($id) {
                return substr($id, 0, 8) == 'graviton' &&
                    strpos($id, 'controller') !== false &&
                    $id !== 'graviton.rest.controller';
            }
        );
        $this->processServices($container, $gravitonServices);
    }
}
