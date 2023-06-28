<?php
/**
 * builds a list which services are backed by solr and which not
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
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
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * map with the weight string incorporated
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->documentMap = $container->get('graviton.document.map');

        $envMap = [
            'SOLR_%s_SORT' => 'sort',
            'SOLR_%s_BF' => 'bf',
            'SOLR_%s_BQ' => 'bq',
            'SOLR_%s_BOOST' => 'boost'
        ];

        $extraParams = [];

        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $solrFields = $document->getSolrFields();
            if (is_array($solrFields) && !empty($solrFields)) {
                $map[$document->getClass()] = $this->getSolrWeightString($solrFields, $document->getClass());

                // extra params
                foreach ($envMap as $envName => $paramName) {
                    $envName = sprintf($envName, strtoupper($this->getCoreName($document->getClass())));
                    if (!empty($_ENV[$envName])) {
                        $extraParams[$document->getClass()][$paramName] = $_ENV[$envName];
                    }
                }
            }
        }

        $container->setParameter('graviton.document.solr.map', $map);
        $container->setParameter('graviton.document.solr.extra_params', $extraParams);
    }

    /**
     * gets the core name from the class
     *
     * @param string $className class name
     *
     * @return string core name
     */
    private function getCoreName(string $className) : string
    {
        $classnameParts = explode('\\', $className);
        return array_pop($classnameParts);
    }

    /**
     * Returns the solr weight string
     *
     * @param array  $solrFields fields
     * @param string $className  class name
     *
     * @return string weight string
     */
    private function getSolrWeightString(array $solrFields, string $className)
    {
        $weights = [];
        foreach ($solrFields as $field) {
            if (is_numeric($field['weight']) && $field['weight'] != 0) {
                $weights[$field['name']] = $field['name'].'^'.$field['weight'];
            }
        }

        // any overrides via env?
        $envName = sprintf("SOLR_%s_WEIGHTS", strtoupper($this->getCoreName($className)));
        if (!empty($_ENV[$envName])) {
            $overrides = explode(' ', $_ENV[$envName]);

            foreach ($overrides as $override) {
                $parts = explode('^', $override);
                if (count($parts) != 2) {
                    continue;
                }

                $weights[$parts[0]] = $override;
            }
        }

        return implode(' ', $weights);
    }
}
