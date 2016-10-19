<?php
/**
 * Configuration definition to be able to define every swagger source via /app/config/config.yml
 */

namespace Graviton\ProxyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * To learn more see {@link http://scm.to/00Yb}
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Defines the structure of the configuration information.
     *
     * Example structure to be used in /app/config/config.yml:
     *
     *   graviton_proxy:
     *     sources:
     *       swagger:
     *         petstore:
     *           prefix: petstore
     *           uri:    http://petstore.swagger.io/v2/swagger.json
     *         another_source:
     *           prefix: myswaggerinstance
     *           uri:    http://swagger.example.org/swagger.json
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('graviton_proxy');

        $rootNode
            ->children()
                ->arrayNode('sources')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                        ->children()
                            ->scalarNode('prefix')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('uri')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('host')->cannotBeEmpty()->end()
                            ->booleanNode('includeBasePath')->defaultValue(false)->end()
                            ->scalarNode('apiKey')->cannotBeEmpty()->end()
                            ->scalarNode('queryStringTemplate')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end() // swagger_proxy
            ->end();

        return $treeBuilder;
    }
}
