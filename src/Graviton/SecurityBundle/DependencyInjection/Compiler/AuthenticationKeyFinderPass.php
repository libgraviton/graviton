<?php
/**
 * Class AuthenticationKeyFinderPass
 */

namespace Graviton\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @api
     *
     * @param ContainerBuilder $container container to look for tags in
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        // collect all tagged services in the entire project
        $taggedServiceIds
            = $container->findTaggedServiceIds('graviton.security.authenticationkey.finder');

        $commandDefinition
            = $container->getDefinition('graviton.security.authenticationkey.finder.command');

        foreach ($taggedServiceIds as $serviceId => $tags) {
            if ($container->hasDefinition($serviceId)) {
                $commandDefinition->addMethodCall(
                    'addService',
                    array(
                        $serviceId,
                        new Reference($serviceId)
                    )
                );
            } else {
                /** @var \Psr\Log\LoggerInterface $logger*/
                if ($container->hasDefinition('logger')) {
                    /** @var \Psr\Log\LoggerInterface $logger */
                    $logger = $container->getDefinition('logger');

                    $logger->warning(
                        sprintf(
                            'The service (%s) is not registered in the application kernel.',
                            $serviceId
                        )
                    );
                }
            }
        }
    }
}
