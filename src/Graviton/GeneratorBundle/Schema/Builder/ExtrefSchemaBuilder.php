<?php
/**
 * ExtrefSchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;
use Symfony\Component\Routing\Router;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class ExtrefSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * gives the SchemaBuilder the opportunity to alter the json schema for that field.
     *
     * @param array $schemaField     the basic field that will be in the schema
     * @param array $fieldDefinition definition as seen by the generator
     * @param array $allDefinitions  all json definitions
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition, array $allDefinitions) : array
    {
        if ($fieldDefinition['type'] == 'extref') {
            $schemaField['type'] = 'string';
            $schemaField['format'] = 'extref';

            if (is_array($fieldDefinition['collection'])) {
                $schemaField['pattern'] = $this->getRegex($fieldDefinition['collection'], $allDefinitions);
                $schemaField['x-collection'] = $fieldDefinition['collection'];
            } else {
                $schemaField['pattern'] = $this->getRegex(['*'], $allDefinitions);
                $schemaField['x-collection'] = ['*'];
            }

            if (isset($fieldDefinition['required'])) {
                $schemaField['nullable'] = ($fieldDefinition['required'] === false);
            }
        }

        return $schemaField;
    }

    /**
     * gets the regex pattern for the defined collections
     *
     * @param array $collections    allowed collections
     * @param array $allDefinitions all json definitions
     *
     * @return string the regex pattern
     *
     * @throws \Exception
     */
    private function getRegex(array $collections, array $allDefinitions) : string
    {
        $basePattern = '((http|https):\/\/)?(.+)?(:\d+)?';
        $allSubPath = '(\/[^\/]+\/.*)';
        $cannotEndInSlash = '[^\/]';

        if (empty($collections) || in_array('*', $collections)) {
            // any allowed!
            return '^'.$basePattern.$allSubPath.$cannotEndInSlash.'$';
        }

        // collect all paths!
        $allowedPaths = [];

        // optional match host and port
        foreach ($collections as $collection) {
            // find the referenced object!
            $routerBase = '';
            foreach ($allDefinitions as $definition) {
                if ($definition->getId() == $collection) {
                    $routerBase = $definition->getRouterBase();
                }
            }

            if (empty($routerBase)) {
                throw new \Exception(
                    sprintf(
                        "Unable to locate the extref referenced collection '%s' among definitions.",
                        $collection
                    )
                );
            }

            if (!str_ends_with($routerBase, '/')) {
                $routerBase .= '/';
            }

            $allowedPaths[] = $routerBase;
        }

        // regex'ify each one
        $allowedPaths = array_map(
            function ($path) {
                return preg_quote($path, '/');
            },
            $allowedPaths
        );

        $allAllowedStartStrings = '('.implode('|', $allowedPaths).')+';
        $anythingAfterService = '(.*)+';

        $fullRegex = '^'.$basePattern.$allAllowedStartStrings.$anythingAfterService.$cannotEndInSlash.'$';

        return $fullRegex;
    }
}
