<?php
/**
 * RuntimeBuilderData
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition\Builder;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\RestBundle\Model\RuntimeDefinition;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class RuntimeBuilderData
{

    /**
     * work on RuntimeDefinition
     *
     * @param RuntimeDefinition $runtimeDefinition runtime def
     * @param JsonDefinition    $definition        definition
     * @param string            $directory         directory
     * @param SplFileInfo       $schemaFile        file info
     */
    public function __construct(
        public RuntimeDefinition $runtimeDefinition,
        public JsonDefinition $definition,
        public string $directory,
        public SplFileInfo $schemaFile
    ) {}

}
