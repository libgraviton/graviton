<?php
/**
 * build a collection_name to routerId mapping for ExtReference Types
 *
 * This is all done the cheap way by just inferring collection names from
 * the available serviecs that are tagged as rest service. This also means
 * we need to stick to the naming conventions already there even more.
 */

namespace Graviton\SwaggerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SwaggerCompilerPass implements CompilerPassInterface
{
    /**
     * create mapping from services
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $map = [];

        $specDef = $container->getDefinition('graviton.rest.apidoc');

        $services = array_keys($container->findTaggedServiceIds('graviton.rest'));
        foreach ($services as $id) {
            $specDef->addMethodCall(
                'addController',
                [
                    $id,
                    new Reference($id)
                ]
            );
        }

        $hans = 3;
    }
}
