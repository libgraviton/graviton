<?php
/**
 * Utils for generating schemas.
 */

namespace Graviton\SchemaBundle;

use Doctrine\Common\Cache\CacheProvider;
use Graviton\I18nBundle\Service\I18nUtils;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Constraint\ConstraintBuilder;
use Graviton\SchemaBundle\Document\Schema;
use Graviton\SchemaBundle\Document\SchemaAdditionalProperties;
use Graviton\SchemaBundle\Document\SchemaType;
use Graviton\SchemaBundle\Service\RepositoryFactory;
use JmesPath\CompilerRuntime;
use JMS\Serializer\Serializer;
use Metadata\MetadataFactoryInterface as SerializerMetadataFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Utils for generating schemas.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaUtils
{

    /**
     * router
     *
     * @var RouterInterface router
     */
    private $router;

    /**
     * serializer
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * mapping service names => route names
     *
     * @var array service mapping
     */
    private $extrefServiceMapping;

    /**
     * event map
     *
     * @var array event map
     */
    private $eventMap;

    /**
     * @var array [document class => [field name -> exposed name]]
     */
    private $documentFieldNames;

    /**
     * @var boolean
     */
    private $schemaVariationEnabled;

    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var SerializerMetadataFactoryInterface
     */
    private $serializerMetadataFactory;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var ConstraintBuilder
     */
    private $constraintBuilder;

    /**
     * @var CompilerRuntime
     */
    private $jmesRuntime;

    /**
     * @var I18nUtils
     */
    private $intUtils;

    /**
     * Constructor
     *
     * @param RepositoryFactory                  $repositoryFactory         Create repos from model class names
     * @param SerializerMetadataFactoryInterface $serializerMetadataFactory Serializer metadata factory
     * @param RouterInterface                    $router                    router
     * @param Serializer                         $serializer                serializer
     * @param array                              $extrefServiceMapping      Extref service mapping
     * @param array                              $eventMap                  eventmap
     * @param array                              $documentFieldNames        Document field names
     * @param boolean                            $schemaVariationEnabled    if schema variations should be enabled
     * @param ConstraintBuilder                  $constraintBuilder         Constraint builder
     * @param CacheProvider                      $cache                     Doctrine cache provider
     * @param CompilerRuntime                    $jmesRuntime               jmespath.php Runtime
     * @param I18nUtils                          $intUtils                  i18n utils
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        SerializerMetadataFactoryInterface $serializerMetadataFactory,
        RouterInterface $router,
        Serializer $serializer,
        array $extrefServiceMapping,
        array $eventMap,
        array $documentFieldNames,
        $schemaVariationEnabled,
        ConstraintBuilder $constraintBuilder,
        CacheProvider $cache,
        CompilerRuntime $jmesRuntime,
        I18nUtils $intUtils
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->router = $router;
        $this->serializer = $serializer;
        $this->extrefServiceMapping = $extrefServiceMapping;
        $this->eventMap = $eventMap;
        $this->documentFieldNames = $documentFieldNames;
        $this->schemaVariationEnabled = (bool) $schemaVariationEnabled;
        $this->constraintBuilder = $constraintBuilder;
        $this->cache = $cache;
        $this->jmesRuntime = $jmesRuntime;
        $this->intUtils = $intUtils;
    }

    /**
     * get schema for an array of models
     *
     * @param string        $modelName name of model
     * @param DocumentModel $model     model
     *
     * @return Schema
     */
    public function getCollectionSchema($modelName, DocumentModel $model)
    {
        $collectionSchema = new Schema;
        $collectionSchema->setTitle(sprintf('Array of %s objects', $modelName));
        $collectionSchema->setType('array');
        $collectionSchema->setItems($this->getModelSchema($modelName, $model));
        return $collectionSchema;
    }

    /**
     * return the schema for a given route
     *
     * @param string        $modelName  name of mode to generate schema for
     * @param DocumentModel $model      model to generate schema for
     * @param boolean       $online     if we are online and have access to mongodb during this build
     * @param boolean       $internal   if true, we generate the schema for internal validation use
     * @param boolean       $serialized if true, it will serialize the Schema object and return a \stdClass instead
     * @param \stdClass     $userData   if given, the userData will be checked for a variation match
     *
     * @return Schema|\stdClass Either a Schema instance or serialized as \stdClass if $serialized is true
     */
    public function getModelSchema(
        $modelName,
        DocumentModel $model,
        $online = true,
        $internal = false,
        $serialized = false,
        $userData = null
    ) {
        $variationName = null;
        if ($this->schemaVariationEnabled === true &&
            $userData instanceof \stdClass &&
            !empty($model->getVariations())
        ) {
            $variationName = $this->getSchemaVariationName($userData, $model->getVariations());
        }

        $languages = [];
        if ($online) {
            $languages = $this->intUtils->getLanguages();
        }
        if (empty($languages)) {
            $languages = [
                $this->intUtils->getDefaultLanguage()
            ];
        }

        $cacheKey = sprintf(
            'schema.%s.%s.%s.%s.%s.%s',
            $model->getEntityClass(),
            (string) $online,
            (string) $internal,
            (string) $serialized,
            (string) $variationName,
            (string) implode('-', $languages)
        );

        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        // build up schema data
        $schema = new Schema;
        $schemaIsCachable = true;

        if (!empty($model->getTitle())) {
            $schema->setTitle($model->getTitle());
        } else {
            if (!is_null($modelName)) {
                $schema->setTitle(ucfirst($modelName));
            } else {
                $reflection = new \ReflectionClass($model);
                $schema->setTitle(ucfirst($reflection->getShortName()));
            }
        }

        $schema->setDescription($model->getDescription());
        $schema->setDocumentClass($model->getDocumentClass());
        $schema->setRecordOriginModifiable($model->getRecordOriginModifiable());
        $schema->setIsVersioning($model->isVersioning());
        $schema->setType('object');
        $schema->setVariations($model->getVariations());
        $schema->setSolrInformation($model->getSolrInformation());

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

        // Init sub searchable fields
        $subSearchableFields = [];

        // reflection
        $documentReflection = new \ReflectionClass($repo->getClassName());
        $documentClass = $documentReflection->newInstance();
        $classShortName = $documentReflection->getShortName();

        // exposed fields
        $documentFieldNames = isset($this->documentFieldNames[$repo->getClassName()]) ?
            $this->documentFieldNames[$repo->getClassName()] :
            [];

        // exposed events..
        if (isset($this->eventMap[$classShortName])) {
            $schema->setEventNames(array_unique($this->eventMap[$classShortName]['events']));
        }

        // don't describe hidden fields
        $requiredFields = $model->getRequiredFields($variationName);
        if (empty($requiredFields) || !is_array($requiredFields)) {
            $requiredFields = [];
        }
        $requiredFields = array_intersect_key($documentFieldNames, array_combine($requiredFields, $requiredFields));

        foreach ($meta->getFieldNames() as $field) {
            // don't describe hidden fields
            if (!isset($documentFieldNames[$field])) {
                continue;
            }

            $isEmptyExtref = false;
            if (is_callable([$documentClass, 'isEmptyExtRefObject'])) {
                $isEmptyExtref = $documentClass->isEmptyExtRefObject();
            }

            // hide realId field (I was aiming at a cleaner solution than the matching realId string initially)
            // hide embedded ID field unless it's required or for internal validation need, back-compatibility.
            // TODO remove !$internal once no clients use it for embedded objects as these id are done automatically
            if (($meta->getTypeOfField($field) == 'id' && $field == 'realId') ||
                (
                    $field == 'id' &&
                    !$internal && $meta->isEmbeddedDocument && !in_array('id', $requiredFields) && $isEmptyExtref
                )
            ) {
                continue;
            }

            $property = new Schema();
            $property->setTitle($model->getTitleOfField($field));
            $property->setDescription($model->getDescriptionOfField($field));
            $property->setType($meta->getTypeOfField($field));
            $property->setGroups($model->getGroupsOfField($field));
            $property->setReadOnly($model->getReadOnlyOfField($field));
            $property->setOnVariation($model->getOnVariaton($field));

            // we only want to render if it's true
            if ($model->getRecordOriginExceptionOfField($field) === true) {
                $property->setRecordOriginException(true);
            }

            if ($meta->getTypeOfField($field) === 'many') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));

                if ($model->hasDynamicKey($field)) {
                    $property->setType('object');

                    if ($online) {
                        // we generate a complete list of possible keys when we have access to mongodb
                        // this makes everything work with most json-schema v3 implementations (ie. schemaform.io)
                        $dynamicKeySpec = $model->getDynamicKeySpec($field);

                        $documentId = $dynamicKeySpec->{'document-id'};
                        $dynamicRepository = $this->repositoryFactory->get($documentId);

                        // put this in invalidate map so when know we have to invalidate when this document is used
                        $invalidateCacheMap[$dynamicRepository->getDocumentName()][] = $cacheKey;

                        $repositoryMethod = $dynamicKeySpec->{'repository-method'};
                        $records = $dynamicRepository->$repositoryMethod();

                        $dynamicProperties = array_map(
                            function ($record) {
                                return $record->getId();
                            },
                            $records
                        );
                        foreach ($dynamicProperties as $propertyName) {
                            $property->addProperty(
                                $propertyName,
                                $this->getModelSchema($field, $propertyModel, $online, $internal)
                            );
                        }

                        // don't cache this schema
                        $schemaIsCachable = false;
                    } else {
                        // swagger case
                        $property->setAdditionalProperties(
                            new SchemaAdditionalProperties(
                                $this->getModelSchema($field, $propertyModel, $online, $internal)
                            )
                        );
                    }
                } else {
                    $property->setItems($this->getModelSchema($field, $propertyModel, $online, $internal));
                    $property->setType('array');
                }
            } elseif ($meta->getTypeOfField($field) === 'one') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));
                $property = $this->getModelSchema($field, $propertyModel, $online, $internal);

                if ($property->getSearchable()) {
                    foreach ($property->getSearchable() as $searchableSubField) {
                        $subSearchableFields[] = $field . '.' . $searchableSubField;
                    }
                }
            } elseif ($meta->getTypeOfField($field) == 'translatable') {
                $property = $this->makeTranslatable($property, $languages);
            } elseif ($meta->getTypeOfField($field) === 'extref') {
                $urls = [];
                $refCollections = $model->getRefCollectionOfField($field);
                foreach ($refCollections as $collection) {
                    if (isset($this->extrefServiceMapping[$collection])) {
                        $urls[] = $this->router->generate(
                            $this->extrefServiceMapping[$collection].'.all',
                            [],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    } elseif ($collection === '*') {
                        $urls[] = '*';
                    }
                }
                $property->setRefCollection($urls);
            } elseif ($meta->getTypeOfField($field) === 'collection') {
                $itemSchema = new Schema();
                $itemSchema->setType($this->getCollectionItemType($meta->name, $field));

                $property->setType('array');
                $property->setItems($itemSchema);
                $property->setFormat(null);
            } elseif ($meta->getTypeOfField($field) === 'datearray') {
                $itemSchema = new Schema();
                $itemSchema->setType('string');
                $itemSchema->setFormat('date-time');

                $property->setType('array');
                $property->setItems($itemSchema);
                $property->setFormat(null);
            } elseif ($meta->getTypeOfField($field) === 'hasharray') {
                $itemSchema = new Schema();
                $itemSchema->setType('object');

                $property->setType('array');
                $property->setItems($itemSchema);
                $property->setFormat(null);
            } elseif ($meta->getTypeOfField($field) === 'translatablearray') {
                $itemSchema = new Schema();
                $itemSchema->setType('object');
                $itemSchema->setFormat('translatable');
                $itemSchema = $this->makeTranslatable($itemSchema, $languages);

                $property->setType('array');
                $property->setItems($itemSchema);
                $property->setFormat(null);
            }

            if (in_array($meta->getTypeOfField($field), $property->getMinLengthTypes())) {
                // make sure a required field cannot be blank
                if (in_array($documentFieldNames[$field], $requiredFields)) {
                    $property->setMinLength(1);
                } else {
                    // in the other case, make sure also null can be sent..
                    $currentType = $property->getType();
                    if ($currentType instanceof SchemaType) {
                        $property->setType(array_merge($currentType->getTypes(), ['null']));
                    } else {
                        $property->setType('null');
                    }
                }
            }

            $property = $this->constraintBuilder->addConstraints($field, $property, $model);

            $schema->addProperty($documentFieldNames[$field], $property);
        }

        /**
         * if we generate schema for internal use; don't have id in required array as
         * it's 'requiredness' depends on the method used (POST/PUT/PATCH) and is checked in checks
         * before validation.
         */
        $idPosition = array_search('id', $requiredFields);
        if ($internal === true && $idPosition !== false && !$meta->isEmbeddedDocument) {
            unset($requiredFields[$idPosition]);
        }

        $schema->setRequired($requiredFields);

        // set additionalProperties to false (as this is our default policy) if not already set
        if (is_null($schema->getAdditionalProperties()) && $online) {
            $schema->setAdditionalProperties(new SchemaAdditionalProperties(false));
        }

        $searchableFields = array_merge($subSearchableFields, $model->getSearchableFields());
        $schema->setSearchable($searchableFields);

        if ($serialized === true) {
            $schema = json_decode($this->serializer->serialize($schema, 'json'));
        }

        if ($schemaIsCachable === true) {
            $this->cache->save($cacheKey, $schema);
        }

        return $schema;
    }

    /**
     * gets the name of the variation to apply based on userdata and the service definition
     *
     * @param \stdClass $userData   user data
     * @param \stdClass $variations variations as defined in schema
     *
     * @return string|null the variation name or null if none
     */
    private function getSchemaVariationName($userData, $variations)
    {
        foreach ($variations as $variationName => $expressions) {
            $results = array_map(
                function ($expression) use ($userData) {
                    return $this->jmesRuntime->__invoke($expression, $userData);
                },
                $expressions
            );

            $results = array_unique($results);

            if (count($results) == 1 && $results[0] === true) {
                return $variationName;
            }
        }

        return null;
    }

    /**
     * turn a property into a translatable property
     *
     * @param Schema   $property  simple string property
     * @param string[] $languages available languages
     *
     * @return Schema
     */
    public function makeTranslatable(Schema $property, $languages)
    {
        $property->setType('object');
        $property->setTranslatable(true);

        array_walk(
            $languages,
            function ($language) use ($property) {
                $schema = new Schema;
                $schema->setType('string');
                $schema->setTitle('Translated String');
                $schema->setDescription('String in ' . $language . ' locale.');
                $property->addProperty($language, $schema);
            }
        );
        $property->setRequired(['en']);
        return $property;
    }

    /**
     * turn a array property into a translatable property
     *
     * @param Schema   $property  simple string property
     * @param string[] $languages available languages
     *
     * @return Schema
     */
    public function makeArrayTranslatable(Schema $property, $languages)
    {
        $property->setType('array');
        $property->setItems($this->makeTranslatable(new Schema(), $languages));
        return $property;
    }

    /**
     * get canonical route to a schema based on a route
     *
     * @param string $routeName route name
     *
     * @return string schema route name
     */
    public static function getSchemaRouteName($routeName)
    {
        $routeParts = explode('.', $routeName);

        $routeType = array_pop($routeParts);
        // check if we need to create an item or collection schema
        $realRouteType = 'canonicalSchema';
        if ($routeType != 'options' && $routeType != 'all') {
            $realRouteType = 'canonicalIdSchema';
        }

        return implode('.', array_merge($routeParts, array($realRouteType)));
    }

    /**
     * Get item type of collection field
     *
     * @param string $className Class name
     * @param string $fieldName Field name
     * @return string|null
     */
    private function getCollectionItemType($className, $fieldName)
    {
        $serializerMetadata = $this->serializerMetadataFactory->getMetadataForClass($className);
        if ($serializerMetadata === null) {
            return null;
        }
        if (!isset($serializerMetadata->propertyMetadata[$fieldName])) {
            return null;
        }

        $type = $serializerMetadata->propertyMetadata[$fieldName]->type;
        return isset($type['name'], $type['params'][0]['name']) && $type['name'] === 'array' ?
            $type['params'][0]['name'] :
            null;
    }
}
