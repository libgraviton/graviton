<?php
/**
 * generates openapi schema
 */

namespace Graviton\GeneratorBundle\Generator;

use Ckr\Util\ArrayMerger;
use Graviton\GeneratorBundle\Event\GenerateSchemaEvent;
use Graviton\GeneratorBundle\Schema\SchemaBuilder;
use Graviton\RestBundle\Service\I18nUtils;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
     * version
     */
    public const string OPENAPI_VERSION = '3.1.0';

    /**
     * @var SchemaBuilder
     */
    private SchemaBuilder $schemaBuilder;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var I18nUtils
     */
    private I18nUtils $intUtils;

    /**
     * @var array
     */
    private array $versionInformation;

    /**
     * set SchemaBuilder
     *
     * @param SchemaBuilder $schemaBuilder schema builder
     *
     * @return void
     */
    public function setSchemaBuilder(SchemaBuilder $schemaBuilder)
    {
        $this->schemaBuilder = $schemaBuilder;
    }

    /**
     * set dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher event dispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * set I18nUtils
     *
     * @param I18nUtils $intUtils int utils
     *
     * @return void
     */
    public function setIntUtils(I18nUtils $intUtils)
    {
        $this->intUtils = $intUtils;
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

        // these reservedFields are predefined and included if in field list!
        $allowedReservedFieldDefinitions = [
            'lastModifiedBy' => [
                'schemaType' => 'string'
            ],
            '_createdBy' => [
                'schemaType' => 'string'
            ],
            'lastModifiedAt' => [
                'schemaType' => 'datetime'
            ],
            '_createdAt' => [
                'schemaType' => 'datetime'
            ]
        ];

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
        $requiredFields = [];

        // always insert id when not subresource!
        if (!$isSubResource) {
            $thisSchema['properties']['id'] = [
                'type' => 'string',
                'title' => 'ID',
                'description' => 'Unique identifier'
            ];
            $reservedFieldNames[] = 'id';
        }

        // add record origin if applicable
        if (isset($parameters['isrecordOriginFlagSet']) && $parameters['isrecordOriginFlagSet'] == true) {
            $thisSchema['properties']['recordOrigin'] = [
                'type' => 'string',
                'title' => 'Record Origin',
                'description' => 'Where this record originated from'
            ];
        }

        // create fields!
        foreach ($parameters['fields'] as $field) {
            $fieldDefinition = [];
            $fieldName = $field['exposedName'];

            if (in_array($fieldName, $reservedFieldNames)) {
                // expose predefined?
                if (!isset($allowedReservedFieldDefinitions[$field['exposedName']])) {
                    // skip!
                    continue;
                }

                // redefine!
                $field = array_merge(
                    $field,
                    $allowedReservedFieldDefinitions[$field['exposedName']]
                );
            }

            $fieldDefinition['type'] = $field['schemaType'];

            $fieldDefinition['description'] = empty($field['description']) ? '@todo replace me' : $field['description'];
            $fieldDefinition['title'] = empty($field['title']) ? '@todo replace me' : $field['title'];

            $isRequired = (isset($field['required']) && $field['required'] == true);

            if ($isRequired) {
                $requiredFields[] = $fieldName;
            }

            // pattern!
            if (!empty($field['valuePattern'])) {
                $fieldDefinition['pattern'] = $field['valuePattern'];
            }

            $fieldDefinition = $this->schemaBuilder->buildSchema(
                $fieldDefinition,
                $field,
                $parameters['jsonDefinitions']
            );

            // if full ref, pass as-is
            if (!empty($fieldDefinition['$ref'])) {
                $thisSchema['properties'][$fieldName] = ['$ref' => $fieldDefinition['$ref']];
                continue;
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

        $thisSchema['additionalProperties'] = false;

        $entityName = SchemaBuilder::getSchemaEntityName($parameters['document'], $parameters['json']->getNamespace());

        // already set?
        if (isset($schema['components']['schemas'][$entityName])) {
            throw new \RuntimeException(
                sprintf(
                    'Schema object "%s" already exists, will not overwrite. Name collision for file %s.',
                    $entityName,
                    $targetFile
                )
            );
        }

        $schema['components']['schemas'][$entityName] = $thisSchema;

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

        $defLanguage = $this->intUtils->getDefaultLanguage();
        foreach ($this->intUtils->getLanguages() as $language) {
            $translatable['properties'][$language] = [
                'type' => 'string',
                'title' => $language,
                'description' => $language.' text'
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
        $arrayMergerFlags = ArrayMerger::FLAG_ALLOW_SCALAR_TO_ARRAY_CONVERSION +
            ArrayMerger::FLAG_PREVENT_DOUBLE_VALUE_WHEN_APPENDING_NUMERIC_KEYS +
            ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY;

        // delete first!
        if ($this->fs->exists($targetFile)) {
            $this->fs->remove($targetFile);
        }

        // our own bundles!
        $directories = [__DIR__.'/../../'];
        if (!empty($baseDir)) {
            $directories[] = $baseDir;
        }

        $output->writeln(
            sprintf('<info>Starting to consolidate openapi schemas in %s</info>', json_encode($directories))
        );

        $files = Finder::create()
            ->files()
            ->in($directories)
            ->path('config/schema')
            ->sortByName()
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

            // if paths are overlapping, we must merge them using
            if (!empty($schema['paths'])) {
                $mainFile['paths'] = ArrayMerger::doMerge(
                    $mainFile['paths'],
                    $schema['paths'],
                    $arrayMergerFlags
                );
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
        foreach ($existingFiles as $filePath => $content) {
            $schema = json_decode($content, true);

            // first, append matching paths
            if (isset($schema['paths'])) {
                $schemaPaths = array_keys($schema['paths']);
                foreach ($schemaPaths as $schemaPath) {
                    if (is_array($mainFile['paths'][$schemaPath])) {
                        $schema['paths'][$schemaPath] = ArrayMerger::doMerge(
                            $mainFile['paths'][$schemaPath],
                            $schema['paths'][$schemaPath],
                            $arrayMergerFlags
                        );
                    }
                }
                $content = json_encode($schema, JSON_UNESCAPED_SLASHES);
            }

            // collect all!
            foreach ($this->getAllReferencedSchemas($content, $mainFile) as $refName => $refSchema) {
                $schema['components']['schemas'][$refName] = $refSchema;
            }

            // remove .tmp ending
            $schemaFile = str_replace(['.tmp', '.yaml'], ['', '.json'], $filePath);

            $this->fs->dumpFile($schemaFile, \json_encode($schema, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));

            $output->writeln(
                sprintf('<info>Wrote file %s</info>', $schemaFile)
            );
        }

        // the global add schema event
        if (!is_null($this->eventDispatcher)) {
            $event = new GenerateSchemaEvent();
            $event = $this->eventDispatcher->dispatch($event, GenerateSchemaEvent::EVENT_NAME);

            foreach ($event->getAdditionalSchemas() as $addedSchema) {
                if (!empty($addedSchema['paths'])) {
                    foreach ($addedSchema['paths'] as $key => $path) {
                        if (isset($mainFile['paths'][$key])) {
                            throw new \LogicException(
                                sprintf(
                                    'The path %s is already defined in main schema and would be '.
                                    'overwritten by GenerateSchemaEvent schema.',
                                    $key
                                )
                            );
                        }
                        $mainFile['paths'][$key] = $path;
                    }
                }

                if (!empty($addedSchema['components']['schemas'])) {
                    foreach ($addedSchema['components']['schemas'] as $name => $obj) {
                        if (isset($mainFile['components']['schemas'][$name])) {
                            throw new \LogicException(
                                sprintf(
                                    'The model name %s is already defined in main schema and would be '.
                                    'overwritten by GenerateSchemaEvent schema.',
                                    $name
                                )
                            );
                        }
                        $mainFile['components']['schemas'][$name] = $obj;
                    }
                }
            }
        }

        ksort($mainFile['paths'], SORT_NATURAL);
        ksort($mainFile['components']['schemas'], SORT_NATURAL);

        // write full schema
        $this->fs->dumpFile($targetFile, \json_encode($mainFile, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));

        $output->writeln(
            sprintf('<info>Wrote full openapi schema to %s</info>', $targetFile)
        );
    }

    /**
     * recursively returns a list of all referenced (by #ref) schemas in the schema
     *
     * @param string $content  start schema
     * @param array  $mainFile the big schema containing all
     * @return array the complete schema
     */
    private function getAllReferencedSchemas(string $content, array $mainFile) : array
    {
        $pattern = '/#\/components\/schemas\/([a-zA-Z0-9\-_]*)/m';
        preg_match_all($pattern, $content, $matches);

        $ret = [];
        foreach (array_unique($matches[1]) as $refName) {
            if (!empty($mainFile['components']['schemas'][$refName])) {
                $thisSchema = $mainFile['components']['schemas'][$refName];
                $ret[$refName] = $thisSchema;

                // recurse!
                $ret = array_merge(
                    $ret,
                    $this->getAllReferencedSchemas(\json_encode($thisSchema, JSON_UNESCAPED_SLASHES), $mainFile)
                );
            }
        }

        return $ret;
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
            'openapi' => self::OPENAPI_VERSION,
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
