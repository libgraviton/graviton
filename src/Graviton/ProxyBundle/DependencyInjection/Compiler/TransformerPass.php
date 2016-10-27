<?php
/**
 * TransformerPass
 */

namespace Graviton\ProxyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TransformerPass
 *
 * @package Graviton\ProxyBundle\Definition\Loader
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TransformerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ContainerBuilder $container Symfony Service container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('graviton.proxy.service.transformationhandler')) {
            return;
        }

        $definition = $container->findDefinition('graviton.proxy.service.transformationhandler');
        $this->findTaggedTransformer($container, $definition, 'graviton.proxy.transformer.request', 'addRequestTransformation');
        $this->findTaggedTransformer($container, $definition, 'graviton.proxy.transformer.response', 'addResponseTransformation');
        $this->findTaggedTransformer($container, $definition, 'graviton.proxy.transformer.schema', 'addSchemaTransformation');
    }

    /**
     * Adds the found services to the TransformationHandler
     *
     * @param ContainerBuilder $container  Symfony Service Container
     * @param Definition       $definition Service the services shall be add to.
     * @param string           $tag        Tag identifying the service to be added
     * @param string           $callable   Name of the method to call to add the tagged service.
     *
     */
    private function findTaggedTransformer(ContainerBuilder $container, Definition $definition, $tag, $callable)
    {
        $taggedServices = $container->findTaggedServiceIds($tag);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    $callable,
                    array(
                        $attributes["alias"],
                        $attributes["endpoint"],
                        new Reference($id)
                    )
                );
            }
        }
    }
}
