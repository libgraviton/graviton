<?php
/**
 * sets the correct model for whoami controller
 */

namespace Graviton\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WhoamiModelCompilerPass implements CompilerPassInterface
{
    /**
     * add our model
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $modelName = $container->getParameter('graviton.security.authentication.provider.model');
        if (!empty($modelName) && $container->has($modelName)) {
            $container
                ->getDefinition('graviton.security.controller.whoami')
                ->addMethodCall(
                    'setModel',
                    [new Reference($modelName)]
                );
        }
    }
}
