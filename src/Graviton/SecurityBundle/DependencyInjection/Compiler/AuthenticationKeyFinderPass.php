<?php
/**
 * Class AuthenticationKeyFinderPass
 */

namespace Graviton\SecurityBundle\DependencyInjection\Compiler;

use Graviton\SecurityBundle\Authentication\Strategies\MultiStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
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

        // Check which Strategy is loaded.
        if (!$container->hasParameter('graviton.security.authentication.strategy')) {
            return;
        }

        // Check which Strategy is used.
        $authService
            = $container->getParameter('graviton.security.authentication.strategy');

        // Only load multi services if configured.
        if (strpos($authService, '.multi') === false) {
            return;
        }

        // Multi Strategy Authentication injection
        $serviceIds
            = $container->getParameter('graviton.security.authentication.strategy.multi.services');

        // Multi service class
        $multiAuth = $container->findDefinition('graviton.security.authentication.strategy.multi');

        // If no service defined we use them all
        if (empty($serviceIds)) {
            $services = $container->findTaggedServiceIds('graviton.security.authenticationkey.finder');
            $serviceIds = array_keys($services);
        }

        // Fetch service and add them to Multi
        foreach ($serviceIds as $id) {
            if ($container->hasDefinition($id)) {
                $multiAuth->addMethodCall(
                    'addStrategy',
                    array(
                        new Reference($id)
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
                            $id
                        )
                    );
                }
            }
        }
    }
}
