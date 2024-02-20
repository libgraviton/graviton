<?php
/**
 * generates openapi schema
 */

namespace Graviton\GeneratorBundle\Generator;

use Graviton\I18nBundle\Service\I18nUtils;
use Graviton\SchemaBundle\Constraint\ConstraintBuilder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * generates openapi schema files for each endpoint and one that sums all up.
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
     * @var I18nUtils
     */
    private I18nUtils $i18nUtils;

    /**
     * @var array
     */
    private array $versionInformation;

    /**
     * set ConstraintBuilder
     *
     * @param ConstraintBuilder $constraintBuilder constraint builder
     *
     * @return void
     */
    public function setConstraintBuilder(ConstraintBuilder $constraintBuilder)
    {
        $this->constraintBuilder = $constraintBuilder;
    }

    /**
     * set I18nUtils
     *
     * @param I18nUtils $i18nUtils
     *
     * @return void
     */
    public function setI18nUtils(I18nUtils $i18nUtils)
    {
        $this->i18nUtils = $i18nUtils;
    }

    /**
     * set version information
     *
     * @param array $versionInformation info
     *
     * @return void
     */
    public function setVersionInformation(array $versionInformation)
    {
        $this->versionInformation = $versionInformation;
    }

    /**
     * generate a schema
     *
     * @param array  $parameters    param
     * @param bool   $isSubResource if sub
     * @param string $targetFile    target
     *
     * @return void
     */
    public function generateSchema(array $parameters, bool $isSubResource, string $targetFile) : void
    {
        $schema = $this->getSchema($targetFile, $parameters['document']);

        $json = $parameters['json'];

        // add document!
        $thisSchema = [
            'type' => 'object',
            'properties' => []
        ];
        if (!empty($json->getDescription())) {
            $thisSchema['description'] = $json->getDescription();
        }

        $reservedFieldNames = $parameters['reservedFieldnames'];
        $reservedFieldNames[] = 'id';
        $requiredFields = [];

        // always insert id
        $thisSchema['properties']['id'] = [
            'type' => 'string',
            'title' => 'ID',
            'description' => 'Unique identifier',
            'nullable' => true
        ];

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

            // if field is a reference, collapse it to a pure $ref!
            if (isset($fieldDefinition['type']) && str_starts_with($fieldDefinition['type'], '#/')) {
                $fieldDefinition = ['$ref' => $fieldDefinition['type']];
            }

            $thisSchema['properties'][$fieldName] = $fieldDefinition;
        }

        if (!empty($requiredFields)) {
            natsort($requiredFields);
            $thisSchema['required'] = array_values($requiredFields);
        }

        // synthetic fields
        if (!$isSubResource && !empty($parameters['syntheticFields'])) {
            foreach ($parameters['syntheticFields'] as $fieldData) {
                $type = $fieldData['type'];

                // shortcut for int
                if ($type == 'int') {
                    $type = 'integer';
                } else {
                    $type = 'string';
                }

                $thisSchema['properties'][$fieldData['name']] = [
                    'type' => $type,
                    'title' => $fieldData['name']
                ];
            }
        }

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
    private function writePaths(array $schema, array $parameters) : array
    {
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

        if ($docName == 'NewEmbedHashTest') {
            $hans = 3;
        }

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
                'summary' => 'Returns '.$docName.' entries.',
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
                'summary' => 'Writes a single '.$docName.' entry.',
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
                'summary' => 'Returns a single '.$docName.' entry.',
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
                'summary' => 'Persists a single '.$docName.' element.',
                'operationId' => 'putOne'.$docName,
                'responses' => $writeResponses,
                'requestBody' => $writeBody,
                'parameters' => [
                    $inputIdParam
                ]
            ];

            $paths[$routerBase.'{id}']['patch'] = [
                'summary' => 'Applies a patch on a single '.$docName.' element.',
                'operationId' => 'patchOne'.$docName,
                'responses' => $writeResponses,
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/GravitonPatchBody'
                            ]
                        ]
                    ]
                ],
                'parameters' => [
                    $inputIdParam
                ]
            ];
            $paths[$routerBase.'{id}']['delete'] = [
                'summary' => 'Deletes a single '.$docName.' element.',
                'operationId' => 'deleteOne'.$docName,
                'responses' => $writeResponses,
                'parameters' => [
                    $inputIdParam
                ]
            ];
        }

        $schema['paths'] = $paths;

        // global object -> translatable
        $translatable = [
            'type' => 'object'
        ];

        $defLanguage = $this->i18nUtils->getDefaultLanguage();
        foreach ($this->i18nUtils->getLanguages() as $language) {
            $translatable['properties'][$language] = [
                'type' => 'string',
                'title' => $language,
                'description' => $language.' text',
                'nullable' => ($language != $defLanguage)
            ];
        }

        $translatable['required'] = [$defLanguage];

        $schema['components']['schemas']['GravitonTranslatable'] = $translatable;

        return $schema;
    }

    /**
     * searches for all single schemas, merges them together and 'fixes' each individual schemas by
     * incorporating all referenced entities into the schema.
     *
     * @param string|null     $baseDir    basedir
     * @param OutputInterface $output     output
     * @param string          $targetFile target file
     *
     * @return void
     */
    public function consolidateAllSchemas(?string $baseDir, OutputInterface $output, string $targetFile) : void
    {
        // delete first!
        if ($this->fs->exists($targetFile)) {
            $this->fs->remove($targetFile);
        }

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
            ->name(['openapi.tmp.json', 'openapi.yaml']); // ending in .tmp!

        $mainFile = $this->getSchema($targetFile, 'Graviton');
        $existingFiles = [];
        $entities = []; // used to track entities and where they're from!

        // these entities can be redeclared!
        $redeclareWhiteList = [
            'GravitonTranslatable'
        ];

        foreach ($files as $file) {
            // already seen the same file?
            if (isset($existingFiles[$file->getRealPath()])) {
                continue;
            }

            $content = $file->getContents();

            if (str_ends_with($file->getRealPath(), '.yaml')) {
                $schema = Yaml::parse($content);
            } else {
                $schema = json_decode($content, true);
            }

            if (!is_array($schema)) {
                continue;
            }

            if (!empty($schema['paths'])) {
                $mainFile['paths'] += $schema['paths'];
            }

            if (!empty($schema['components']['schemas'])) {
                foreach ($schema['components']['schemas'] as $entityName => $entitySchema) {
                    if (isset($entities[$entityName]) && !in_array($entityName, $redeclareWhiteList)) {
                        throw new \LogicException(
                            sprintf(
                                'The entity %s was already defined in file %s and would be redefined in file %s',
                                $entityName,
                                $entities[$entityName],
                                $file->getRealPath()
                            )
                        );
                    } else {
                        $entities[$entityName] = $file->getRealPath();
                        $mainFile['components']['schemas'][$entityName] = $entitySchema;
                    }
                }
            }

            $existingFiles[$file->getRealPath()] = json_encode($schema, JSON_UNESCAPED_SLASHES);
        }

        // 2nd pass -> fix all missing references
        $pattern = '/#\/components\/schemas\/([a-zA-Z0-9]*)/m';
        foreach ($existingFiles as $filePath => $content) {
            // find needed entities
            preg_match_all($pattern, $content, $matches);

            $schema = json_decode($content, true);

            // collect all!
            foreach (array_unique($matches[1]) as $refName) {
                if (!empty($mainFile['components']['schemas'][$refName])) {
                    $schema['components']['schemas'][$refName] = $mainFile['components']['schemas'][$refName];
                }
            }

            // remove .tmp ending
            $schemaFile = str_replace(['.tmp', '.yaml'], ['', '.json'], $filePath);

            $this->fs->dumpFile($schemaFile, \json_encode($schema, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
            $output->writeln('Wrote file '.$schemaFile);
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
    private function getSchema(string $filename, string $docName) : array
    {
        if ($this->fs->exists($filename)) {
            return \json_decode(file_get_contents($filename), true);
        }

        $base = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Endpoint for '.$docName.' entries.',
                'version' => $this->versionInformation['self']
            ],
            'paths' => [],
            'components' => [
                'schemas' => []
            ]
        ];

        return $base;
    }
}
