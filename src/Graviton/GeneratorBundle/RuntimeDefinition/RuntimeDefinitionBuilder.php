<?php
/**
 * RuntimeDefinitionBuilder
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
class RuntimeDefinitionBuilder
{
    /**
     * @var RuntimeDefinitionBuilderAbstract[]
     */
    private array $builders = [];

    /**
     * Add builder
     *
     * @param RuntimeDefinitionBuilderAbstract $builder builder
     *
     * @return void
     */
    public function addBuilder(RuntimeDefinitionBuilderAbstract $builder)
    {
        $this->builders[] = $builder;
    }

    /**
     * work on RuntimeDefinition
     *
     * @param JsonDefinition $definition definition
     * @param string         $directory  directory
     * @param SplFileInfo    $schemaFile file info
     *
     * @return RuntimeDefinition runtime info
     */
    public function build(JsonDefinition $definition, string $directory, SplFileInfo $schemaFile) : RuntimeDefinition
    {
        $runtime = new RuntimeDefinition();

        foreach ($this->builders as $builder) {
            $builder->build(
                $runtime,
                $definition,
                $directory,
                $schemaFile
            );
        }

        return $runtime;
    }
}
