<?php
/**
 * EventNamesBuilder
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition\Builder;

use Graviton\GeneratorBundle\RuntimeDefinition\RuntimeDefinitionBuilderAbstract;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EventNamesBuilder extends RuntimeDefinitionBuilderAbstract
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
        $mainName = $data->definition->getId();
        if (empty($mainName)) {
            throw new \RuntimeException(
                sprintf('Definition is invalid, has no "id" property, dir %s"', $data->directory)
            );
        }

        $name = strtolower($mainName);

        $data->runtimeDefinition->setRestEventNames(
            [
                'put' => "document.{$name}.{$name}.update",
                'patch' => "document.{$name}.{$name}.update",
                'post' => "document.{$name}.{$name}.create",
                'delete' => "document.{$name}.{$name}.delete"
            ]
        );
    }
}
