<?php
/**
 * Configuration definition to be able to define every shown endpoint on the graviton main page.
 */
namespace Graviton\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * To learn more see {@link http://scm.to/00Yb}
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Defines the structure of the configuration information.
     *
     * Example structure to be used in /app/config/config.yml:
     *
     *   graviton_core:
     *       service_name:
     *           - "graviton.service.first.example"
     *           - "graviton.service.second.example"
     *       uri_whitelist:
     *           - "/path/to/first/"
     *           - "/path/to/second/"
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('graviton_core');

        $rootNode
            ->children()
                ->arrayNode('service_name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('uri_whitelist')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
