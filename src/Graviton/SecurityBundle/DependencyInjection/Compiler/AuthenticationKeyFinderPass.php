<?php
/**
 * Created by PhpStorm.
 * User: lapistano
 * Date: 13.02.15
 * Time: 10:31
 */

namespace Graviton\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AuthenticationKeyFinderPass
 *
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
     * @param ContainerBuilder $container
     *
     * @api
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

                if ($container->hasDefinition('logger')) {
                    $logger = $container->getDefinition('logger');

                    $logger->warming(
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
