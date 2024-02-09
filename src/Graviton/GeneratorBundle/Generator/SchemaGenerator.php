<?php
/**
 * generates openapi schema
 */

namespace Graviton\GeneratorBundle\Generator;

use Graviton\SchemaBundle\Constraint\ConstraintBuilder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * bundle containing various code generators
 *
 * This code is more or less loosley based on SensioBundleGenerator. It could
 * use some refactoring to duplicate less for that, but this is how i finally
 * got a working version.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaGenerator extends AbstractGenerator
{

    /**
     * @var ConstraintBuilder
     */
    private ConstraintBuilder $constraintBuilder;

    /**
     * set ConstraintBuilder
     *
     * @param ConstraintBuilder $constraintBuilder constraint builder
     *
     * @return void
     */
    public function setConstraintBuilder(ConstraintBuilder $constraintBuilder) {
        $this->constraintBuilder = $constraintBuilder;
    }

    /**
     * generate a schema
     *
     * @param array $parameters   param
     * @param bool $isSubResource if sub
     * @param string $targetFile  target
     *
     * @return void
     */
    public function generateSchema(array $parameters, bool $isSubResource, string $targetFile) : void
    {
        $schema = $this->getSchema($targetFile, $parameters['document']);

        // add document!
        $thisSchema = [
            'type' => 'object',
            'properties' => []
        ];

        $reservedFieldNames = $parameters['reservedFieldnames'];
        $requiredFields = [];

        // create fields!
        foreach ($parameters['fields'] as $field) {
            $fieldDefinition = [];
            $fieldName = $field['exposedName'];

            if (in_array($fieldName, $reservedFieldNames)) {
                // skip!
                continue;
            }

            $fieldDefinition['type'] = $field['schemaType'];

            $fieldDefinition['description'] = empty($field['description']) ? '@todo replace me' : $field['description'];
            $fieldDefinition['title'] = empty($field['title']) ? '@todo replace me' : $field['title'];

            $isRequired = (isset($field['required']) && $field['required'] == true);

            if ($isRequired) {
                $requiredFields[] = $fieldName;
            } else {
                $fieldDefinition['nullable'] = true;
            }

            // pattern!
            if (!empty($field['valuePattern'])) {
                $fieldDefinition['pattern'] = $field['valuePattern'];
            }

            $fieldDefinition = $this->constraintBuilder->buildSchema($fieldDefinition, $field);

            $thisSchema['properties'][$fieldName] = $fieldDefinition;
        }

        natsort($requiredFields);
        $thisSchema['required'] = $requiredFields;

        $schema['components']['schemas'][$parameters['document']] = $thisSchema;

        if (!$isSubResource) {
            $schema = $this->writePaths($schema, $parameters);
        }

        // write
        $this->fs->dumpFile($targetFile, \json_encode($schema, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
    }

    /**
     * add the paths.
     *
     * @param array $schema     schema
     * @param array $parameters params
     *
     * @return array schema
     */
    private function writePaths(array $schema, array $parameters) : array {
        // main route!
        $routerBase = $parameters['json']->getRouterBase();
        $isReadOnly = $parameters['json']->isReadOnlyService();

        if (empty($routerBase)) {
            return $schema;
        }

        if (!str_ends_with($routerBase, '/')) {
            $routerBase .= '/';
        }

        $paths = [];

        // this is the resource name!
        $docName = $parameters['document'];

        $writeResponses = [
            201 => [
                'description' => 'Successful operation'
            ],
            400 => [
                'description' => 'Bad request.'
            ],
            404 => [
                'description' => 'Entry not found.',
            ]
        ];

        // is the same for put and post
        $writeBody = [
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/'.$docName
                    ]
                ]
            ]
        ];

        // "ALL" action
        $paths[$routerBase] = [
            'get' => [
                'summary' => 'Returns "'.$docName.'" entries.',
                'operationId' => 'getAll'.$docName,
                'responses' => [
                    200 => [
                        'description' => 'successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        '$ref' => '#/components/schemas/'.$docName
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if (!$isReadOnly) {
            $paths[$routerBase]['post'] = [
                'summary' => 'Writes a single "'.$docName.'" entry.',
                'operationId' => 'post'.$docName,
                'requestBody' => $writeBody,
                'responses' => $writeResponses
            ];
        }

        $inputIdParam = [
            'name' => 'id',
            'in' => 'path',
            'description' => 'ID of entry.',
            'required' => true,
            'schema' => [
                'type' => 'string'
            ]
        ];

        // "ONE" action
        $paths[$routerBase.'{id}'] = [
            'get' => [
                'summary' => 'Returns a single "'.$docName.'" element.',
                'operationId' => 'getOne'.$docName,
                'responses' => [
                    200 => [
                        'description' => 'successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/'.$docName]
                            ]
                        ]
                    ],
                    400 => [
                        'description' => 'Invalid ID supplied.',
                    ],
                    404 => [
                        'description' => 'Entry not found.',
                    ]
                ],
                'parameters' => [
                    $inputIdParam
                ]
            ]
        ];

        if (!$isReadOnly) {
            $paths[$routerBase.'{id}']['put'] = [
                'summary' => 'Persists a single "'.$docName.'" element.',
                'operationId' => 'putOne'.$docName,
                'responses' => $writeResponses,
                'requestBody' => $writeBody,
                'parameters' => [
                    $inputIdParam
                ]
            ];
            $paths[$routerBase.'{id}']['delete'] = [
                'summary' => 'Deletes a single "'.$docName.'" element.',
                'operationId' => 'deleteOne'.$docName,
                'responses' => $writeResponses,
                'parameters' => [
                    $inputIdParam
                ]
            ];
        }

        $schema['paths'] = $paths;

        return $schema;
    }

    public function consolidateAllSchemas(?string $baseDir, OutputInterface $output, string $targetFile) : void {
        // our own bundles!
        $directories = [__DIR__.'/../../'];
        if (!empty($baseDir)) {
            $directories[] = $baseDir;
        }

        $output->writeln('Starting to consolidate openapi schemas in '.json_encode($directories));

        $files = Finder::create()
            ->files()
            ->in($directories)
            ->path('config/schema')
            ->name('openapi.json');

        $allSchemas = [];
        $mainFile = $this->getSchema($targetFile, 'Graviton');
        $existingFiles = [];

        foreach ($files as $file) {
            $content = $file->getContents();

            $schema = json_decode($content, true);
            if (!is_array($schema)) {
                continue;
            }

            if (!empty($schema['paths'])) {
                $mainFile['paths'] += $schema['paths'];
            }

            if (!empty($schema['components']['schemas'])) {
                $mainFile['components']['schemas'] += $schema['components']['schemas'];
            }

            $existingFiles[] = $file;
        }

        // 2nd pass -> fix all missing references
        $pattern = '/#\/components\/schemas\/([a-zA-Z0-9]*)/m';
        foreach ($existingFiles as $file) {
            $content = $file->getContents();

            // find needed entities
            preg_match_all($pattern, $content, $matches);

            if (empty($matches[1])) {
                continue;
            }

            $schema = json_decode($content, true);

            // collect all!
            foreach ($matches[1] as $refName) {
                if (!empty($mainFile['components']['schemas'][$refName])) {
                    $schema['components']['schemas'][$refName] = $mainFile['components']['schemas'][$refName];
                }
            }

            $this->fs->dumpFile($file->getPathname(), \json_encode($schema, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
            $output->writeln('Rewrote file '.$file->getPathname());
        }

        // write full schema
        $this->fs->dumpFile($targetFile, \json_encode($mainFile, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
        $output->writeln("Wrote full openapi schema to ".$targetFile);

    }


    /**
     * Returns either new schema or the one from file
     *
     * @param string $filename filename
     * @param string $docName  docname
     *
     * @return array schema
     */
    private function getSchema(string $filename, string $docName) : array {
        if ($this->fs->exists($filename)) {
            return \json_decode(file_get_contents($filename), true);
        }

        $base = [
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'Endpoint for '.$docName.' entries.',
                'version' => 'TO_BE_DEFINED'
            ],
            'paths' => [],
            'components' => [
                'schemas' => []
            ]
        ];

        return $base;
    }
}
