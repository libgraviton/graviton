<?php

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * collects all the controller that where tagged as rest controller
 *
 * This collection can then be used to generate a list of routes in
 * Graviton\RestBundle\Routing\Loader\BasicLoader.
 */
class RouteCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(
            'graviton.rest.controllercollection'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'graviton.rest'
    );
	echo 'asdfasdfadsf';
	foreach ($taggedServices as $id => $attributes) {
		echo '-______-'.PHP_EOL;
		echo $id.PHP_EOL;
            $definition->addMethodCall(
                'addController',
                array(new Reference($id))
            );
        }
    }
}
