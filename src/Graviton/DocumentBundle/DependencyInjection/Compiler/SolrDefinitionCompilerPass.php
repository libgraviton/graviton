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

        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $solrFields = $document->getSolrFields();
            if (is_array($solrFields) && !empty($solrFields)) {
                $map[$document->getClass()] = $this->getSolrWeightString($solrFields);
            }
        }

        var_dump($map);

        $container->setParameter('graviton.document.solr.map', $map);
    }

    /**
     * Returns the solr weight string
     *
     * @param array $solrFields fields
     *
     * @return string weight string
     */
    private function getSolrWeightString(array $solrFields)
    {
        $weights = [];
        foreach ($solrFields as $field) {
            $weights[] = $field['name'].'^'.$field['weight'];
        }

        return implode(' ', $weights);
    }
}
