<?php
/**
 * find all services tagged as 'graviton.reference_handler_types' and store an array of types
 */

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReferenceHandlerCompilerPass implements CompilerPassInterface
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
        $services = array_keys($container->findTaggedServiceIds('graviton.reference_handler_types'));
        $registry = $container->getDefinition('jms_serializer.handler_registry');

        $types = [];
        foreach ($services as $service) {
            $type = $container->getDefinition($service)->getClass();
            $registry->addMethodCall(
                'registerHandler',
                array(
                    'serialization',
                    $type,
                    'json',
                    array(new Reference('graviton.rest.subscriber.referencehandlerevent'), 'serialize')
                )
            );
            $types[] = $type;
        }
        $container->setParameter(
            'graviton.rest.subscriber.referencehandlerevent.types',
            $types
        );
    }
}
