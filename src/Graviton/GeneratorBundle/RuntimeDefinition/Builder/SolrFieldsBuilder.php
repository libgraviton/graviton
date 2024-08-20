<?php
/**
 * SolrFieldsBuilder
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition\Builder;

use Graviton\GeneratorBundle\RuntimeDefinition\RuntimeDefinitionBuilderAbstract;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrFieldsBuilder extends RuntimeDefinitionBuilderAbstract
{

    /**
     * work on RuntimeDefinition
     *
     * @param RuntimeBuilderData $data data
     *
     * @return void
     */
    public function build(RuntimeBuilderData $data) : void
    {
        $solrFields = $data->definition->getSolrFields();
        if (!empty($solrFields)) {
            $data->runtimeDefinition->setSolrFields($solrFields);
        }
    }
}
