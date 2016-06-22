<?php
/**
 * Utils for generating schemas.
 */

namespace Graviton\SchemaBundle;

use Doctrine\Common\Cache\CacheProvider;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\I18nBundle\Document\Language;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Constraint\ConstraintBuilder;
use Graviton\SchemaBundle\Document\Schema;
use Graviton\SchemaBundle\Document\SchemaAdditionalProperties;
use Graviton\SchemaBundle\Document\SchemaType;
use Graviton\SchemaBundle\Service\RepositoryFactory;
use Metadata\MetadataFactoryInterface as SerializerMetadataFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Utils for generating schemas.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaUtils
{

    /**
     * language repository
     *
     * @var LanguageRepository repository
     */
    private $languageRepository;

    /**
     * router
     *
     * @var RouterInterface router
     */
    private $router;

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
     * @var string
     */
    private $defaultLocale;

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
     * @var string
     */
    private $cacheInvalidationMapKey;

    /**
     * @var ConstraintBuilder
     */
    private $constraintBuilder;

    /**
     * Constructor
     *
     * @param RepositoryFactory                  $repositoryFactory         Create repos from model class names
     * @param SerializerMetadataFactoryInterface $serializerMetadataFactory Serializer metadata factory
     * @param LanguageRepository                 $languageRepository        repository
     * @param RouterInterface                    $router                    router
     * @param array                              $extrefServiceMapping      Extref service mapping
     * @param array                              $eventMap                  eventmap
     * @param array                              $documentFieldNames        Document field names
     * @param string                             $defaultLocale             Default Language
     * @param ConstraintBuilder                  $constraintBuilder         Constraint builder
     * @param CacheProvider                      $cache                     Doctrine cache provider
     * @param string                             $cacheInvalidationMapKey   Cache invalidation map cache key
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        SerializerMetadataFactoryInterface $serializerMetadataFactory,
        LanguageRepository $languageRepository,
        RouterInterface $router,
        array $extrefServiceMapping,
        array $eventMap,
        array $documentFieldNames,
        $defaultLocale,
        ConstraintBuilder $constraintBuilder,
        CacheProvider $cache,
        $cacheInvalidationMapKey
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->languageRepository = $languageRepository;
        $this->router = $router;
        $this->extrefServiceMapping = $extrefServiceMapping;
        $this->eventMap = $eventMap;
        $this->documentFieldNames = $documentFieldNames;
        $this->defaultLocale = $defaultLocale;
        $this->constraintBuilder = $constraintBuilder;
        $this->cache = $cache;
        $this->cacheInvalidationMapKey = $cacheInvalidationMapKey;
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
     * @param string        $modelName name of mode to generate schema for
     * @param DocumentModel $model     model to generate schema for
     * @param boolean       $online    if we are online and have access to mongodb during this build
     * @param boolean       $internal  if true, we generate the schema for internal validation use
     *
     * @return Schema
     */
    public function getModelSchema($modelName, DocumentModel $model, $online = true, $internal = false)
    {
        $cacheKey = 'schema'.$model->getEntityClass().'.'.(string) $online.'.'.(string) $internal;

        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        $invalidateCacheMap = [];
        if ($this->cache->contains($this->cacheInvalidationMapKey)) {
            $invalidateCacheMap = $this->cache->fetch($this->cacheInvalidationMapKey);
        }

        // build up schema data
        $schema = new Schema;

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
        $schema->setType('object');

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

        // Init sub searchable fields
        $subSearchableFields = array();

        // look for translatables in document class
        $documentReflection = new \ReflectionClass($repo->getClassName());
        if ($documentReflection->implementsInterface('Graviton\I18nBundle\Document\TranslatableDocumentInterface')) {
            /** @var TranslatableDocumentInterface $documentInstance */
            $documentInstance = $documentReflection->newInstanceWithoutConstructor();
            $translatableFields = array_merge(
                $documentInstance->getTranslatableFields(),
                $documentInstance->getPreTranslatedFields()
            );
        } else {
            $translatableFields = [];
        }

        if (!empty($translatableFields)) {
            $invalidateCacheMap[$this->languageRepository->getClassName()][] = $cacheKey;
        }

        // exposed fields
        $documentFieldNames = isset($this->documentFieldNames[$repo->getClassName()]) ?
            $this->documentFieldNames[$repo->getClassName()] :
            [];

        if ($online) {
            $languages = array_map(
                function (Language $language) {
                    return $language->getId();
                },
                $this->languageRepository->findAll()
            );
        } else {
            $languages = [
                $this->defaultLocale
            ];
        }

        // exposed events..
        $classShortName = $documentReflection->getShortName();
        if (isset($this->eventMap[$classShortName])) {
            $schema->setEventNames(array_unique($this->eventMap[$classShortName]['events']));
        }

        $requiredFields = [];
        $modelRequiredFields = $model->getRequiredFields();
        if (is_array($modelRequiredFields)) {
            foreach ($modelRequiredFields as $field) {
                // don't describe hidden fields
                if (!isset($documentFieldNames[$field])) {
                    continue;
                }

                $requiredFields[] = $documentFieldNames[$field];
            }
        }

        foreach ($meta->getFieldNames() as $field) {
            // don't describe hidden fields
            if (!isset($documentFieldNames[$field])) {
                continue;
            }
            // hide realId field (I was aiming at a cleaner solution than the macig realId string initially)
            if ($meta->getTypeOfField($field) == 'id' && $field == 'realId') {
                continue;
            }

            $property = new Schema();
            $property->setTitle($model->getTitleOfField($field));
            $property->setDescription($model->getDescriptionOfField($field));

            $property->setType($meta->getTypeOfField($field));
            $property->setReadOnly($model->getReadOnlyOfField($field));

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
                                $this->getModelSchema($field, $propertyModel, $online)
                            );
                        }
                    } else {
                        // swagger case
                        $property->setAdditionalProperties(
                            new SchemaAdditionalProperties($this->getModelSchema($field, $propertyModel, $online))
                        );
                    }
                } else {
                    $property->setItems($this->getModelSchema($field, $propertyModel, $online));
                    $property->setType('array');
                }
            } elseif ($meta->getTypeOfField($field) === 'one') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));
                $property = $this->getModelSchema($field, $propertyModel, $online);

                if ($property->getSearchable()) {
                    foreach ($property->getSearchable() as $searchableSubField) {
                        $subSearchableFields[] = $field . '.' . $searchableSubField;
                    }
                }
            } elseif (in_array($field, $translatableFields, true)) {
                $property = $this->makeTranslatable($property, $languages);
            } elseif (in_array($field.'[]', $translatableFields, true)) {
                $property = $this->makeArrayTranslatable($property, $languages);
            } elseif ($meta->getTypeOfField($field) === 'extref') {
                $urls = array();
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
                $property->setType('array');
                $itemSchema->setType($this->getCollectionItemType($meta->name, $field));

                $property->setItems($itemSchema);
                $property->setFormat(null);
            } elseif ($meta->getTypeOfField($field) === 'datearray') {
                $itemSchema = new Schema();
                $property->setType('array');
                $itemSchema->setType('string');
                $itemSchema->setFormat('date-time');

                $property->setItems($itemSchema);
                $property->setFormat(null);
            } elseif ($meta->getTypeOfField($field) === 'hasharray') {
                $itemSchema = new Schema();
                $itemSchema->setType('object');

                $property->setType('array');
                $property->setItems($itemSchema);
                $property->setFormat(null);
            } elseif (in_array($meta->getTypeOfField($field), $property->getMinLengthTypes())) {
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

        if ($meta->isEmbeddedDocument && !in_array('id', $model->getRequiredFields())) {
            $schema->removeProperty('id');
        }

        /**
         * if we generate schema for internal use; don't have id in required array as
         * it's 'requiredness' depends on the method used (POST/PUT/PATCH) and is checked in checks
         * before validation.
         */
        $idPosition = array_search('id', $requiredFields);
        if ($internal === true && $idPosition !== false) {
            unset($requiredFields[$idPosition]);
        }

        $schema->setRequired($requiredFields);

        // set additionalProperties to false (as this is our default policy) if not already set
        if (is_null($schema->getAdditionalProperties()) && $online) {
            $schema->setAdditionalProperties(new SchemaAdditionalProperties(false));
        }

        $searchableFields = array_merge($subSearchableFields, $model->getSearchableFields());
        $schema->setSearchable($searchableFields);

        $this->cache->save($cacheKey, $schema);
        $this->cache->save($this->cacheInvalidationMapKey, $invalidateCacheMap);

        return $schema;
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
