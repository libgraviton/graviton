<?php

namespace Graviton\SecurityBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AuthenticationPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServiceIds
            = $container->findTaggedServiceIds('graviton.security.authentication.strategy');

        $strategyDefinition
            = $container->getDefinition('graviton.sercurity.authentication.strategy.collection');

        foreach ($taggedServiceIds as $serviceId => $tags) {

            $strategyDefinition->addMethodCall('add', array(new Reference($serviceId)));
        }
    }

}
