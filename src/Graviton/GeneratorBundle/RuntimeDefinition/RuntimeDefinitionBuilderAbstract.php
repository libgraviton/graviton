<?php
/**
 * RuntimeDefinitionBuilderAbstract
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\RestBundle\Model\RuntimeDefinition;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class RuntimeDefinitionBuilderAbstract
{
    /**
     * work on RuntimeDefinition
     *
     * @param RuntimeDefinition $runtimeDefinition runtime def
     * @param JsonDefinition    $definition        definition
     * @param string            $directory         directory
     * @param SplFileInfo       $schemaFile        file info
     */
    abstract public function build(
        RuntimeDefinition $runtimeDefinition,
        JsonDefinition $definition,
        string $directory,
        SplFileInfo $schemaFile
    ) : void;
}
