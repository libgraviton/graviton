<?php
/**
 * builds a list which services are backed by solr and which not
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\Service\SolrQuery;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrDefinitionCompilerPass implements CompilerPassInterface
{

    /**
     * map with the weight string incorporated
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $envMap = [
            'SORT' => 'sort',
            'BF' => 'bf',
            'BQ' => 'bq',
            'BOOST' => 'boost'
        ];

        $envMapExtraParams = [
            SolrQuery::EXTRA_PARAM_FUZZY_BRIDGE => 'int',
            SolrQuery::EXTRA_PARAM_LITERAL_BRIDGE => 'int',
            SolrQuery::EXTRA_PARAM_WILDCARD_BRIDGE => 'int',
            SolrQuery::EXTRA_PARAM_ANDIFY_TERMS => 'bool',
            SolrQuery::EXTRA_PARAM_WEIGHTS => 'string'
        ];

        $extraParams = [];

        foreach ($_ENV as $varName => $varValue) {
            if (!str_starts_with($varName, 'SOLR_')) {
                continue;
            }

            preg_match('/SOLR_([a-zA-Z]*)_(.*)/', $varName, $matches);

            if (count($matches) != 3) {
                continue;
            }

            $className = $matches[1];
            $settingName = $matches[2];

            // setting?
            if (isset($envMap[$settingName])) {
                $extraParams[$className][$envMap[$settingName]] = $varValue;
                continue;
            }

            // another setting?
            if (isset($envMapExtraParams[$settingName])) {
                $value = match ($envMapExtraParams[$settingName]) {
                    "int" => (int) $varValue,
                    "bool" => ($varValue == 'true'),
                    default => $varValue
                };

                $extraParams[$className][$settingName] = $value;
            }
        }

        $container->setParameter('graviton.document.solr.extra_params', $extraParams);
    }
}
