<?php
/**
 * RqlQueryRoutesCompilerPass class file
 */

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlQueryRoutesCompilerPass implements CompilerPassInterface
{
    /**
     * Find "allAction" routes and set it to allowed routes for RQL parsing
     *
     * @param ContainerBuilder $container Container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $routes = [];
        foreach ($container->getParameter('graviton.rest.services') as $service => $params) {
            list($app, $bundle, , $entity) = explode('.', $service);
            $routes[] = implode('.', [$app, $bundle, 'rest', $entity, 'all']);
        }

        $container->setParameter('graviton.rest.listener.rqlqueryrequestlistener.allowedroutes', $routes);
    }
}
