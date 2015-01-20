<?php

namespace Graviton\SecurityBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AuthenticationPass implements CompilerPassInterface
{
    /**
     * Finds services tagged with "graviton.security.authentication.strategy" or
     * defined in parameters as "graviton-security.authentication.services" and adds them to
     * the "graviton.sercurity.authentication.strategy.collection".
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $strategies = array();

        if($container->hasParameter('graviton-security.authentication.services')) {
            $strategies = $container->getParameter("graviton-security.authentication.services");
        }

        $taggedServiceIds = array_unique(array_merge(
            $strategies,
            array_keys($container->findTaggedServiceIds('graviton.security.authentication.strategy'))
        ));

        $strategyDefinition
            = $container->getDefinition('graviton.sercurity.authentication.strategy.collection');

        foreach ($taggedServiceIds as $serviceId) {

            $strategyDefinition->addMethodCall('add', array(new Reference($serviceId)));
        }
    }

}
