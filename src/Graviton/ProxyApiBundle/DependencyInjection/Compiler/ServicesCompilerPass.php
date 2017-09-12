<?php
/**
 * RqlQueryRoutesCompilerPass class file
 */

namespace Graviton\ProxyApiBundle\DependencyInjection\Compiler;

use Graviton\ProxyApiBundle\Helper\ArrayDefinitionMapper;
use Graviton\ProxyApiBundle\Manager\ProxyManager;
use Graviton\ProxyApiBundle\Model\ProxyModel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ServicesCompilerPass implements CompilerPassInterface
{
    /** @var ContainerBuilder */
    protected $container;
    /**
     * Find "allAction" routes and set it to allowed routes for RQL parsing
     *
     * @param ContainerBuilder $container Container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $mapper = new ArrayDefinitionMapper();
        $this->container = $container;

        /** @var Definition | ProxyManager $proxyManager */
        $proxyManager = $container->findDefinition('graviton.proxy_api.proxy_manager');

        /** @var Definition | ProxyModel $proxyModelOrg */
        $proxyModelOrg = $container->findDefinition('graviton.proxy_api.proxy_model');

        // Create available service objects
        $services = [];
        foreach ($container->getParameter('graviton.proxy_api.sources') as $name => $definition) {
            // Service definitions
            $proxyModel = clone $proxyModelOrg;
            $definition['name'] = $name;
            $definition = $this->setServiceProcessors($definition);
            $mapper->map($definition, $proxyModel);
            $services[$name] = $proxyModel;
        }

        $proxyManager->addMethodCall('addServices', array($services));
    }

    /**
     * Create defaults service if not set.
     *
     * @param array $definition to be mapped
     * @return mixed
     */
    private function setServiceProcessors($definition)
    {
        // Model definition to Real service definition
        $defaults = [
            'preProcessorService' => 'graviton.proxy_api.processor.pre',
            'proxyProcessorService' => 'graviton.proxy_api.processor.proxy',
            'postProcessorService' => 'graviton.proxy_api.processor.post',
        ];

        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $definition)) {
                $definition[$key] = $this->container->findDefinition($default);
            } else {
                $definition[$key] = $this->container->findDefinition($definition[$key]);
            }
        }
        return $definition;
    }
}
