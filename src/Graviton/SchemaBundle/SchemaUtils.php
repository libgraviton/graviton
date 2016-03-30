<?php
/**
 * Utils for generating schemas.
 */

namespace Graviton\SchemaBundle;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\I18nBundle\Document\Language;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\Document\Schema;
use Graviton\SchemaBundle\Service\RepositoryFactory;
use Metadata\MetadataFactoryInterface as SerializerMetadataFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

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
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        SerializerMetadataFactoryInterface $serializerMetadataFactory,
        LanguageRepository $languageRepository,
        RouterInterface $router,
        array $extrefServiceMapping,
        array $eventMap,
        array $documentFieldNames,
        $defaultLocale
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->languageRepository = $languageRepository;
        $this->router = $router;
        $this->extrefServiceMapping = $extrefServiceMapping;
        $this->eventMap = $eventMap;
        $this->documentFieldNames = $documentFieldNames;
        $this->defaultLocale = $defaultLocale;
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
     *
     * @return Schema
     */
    public function getModelSchema($modelName, DocumentModel $model, $online = true)
    {
        // build up schema data
        $schema = new Schema;

        if (!empty($model->getTitle())) {
            $schema->setTitle($model->getTitle());
        } else {
            $schema->setTitle(ucfirst($modelName));
        }

        $schema->setDescription($model->getDescription());
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
                        // in the swagger case we can use additionPorerties which where introduced by json-schema v4
                        $property->setAdditionalProperties($this->getModelSchema($field, $propertyModel, $online));
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
                            true
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
            }
            $schema->addProperty($documentFieldNames[$field], $property);
        }

        if ($meta->isEmbeddedDocument && !in_array('id', $model->getRequiredFields())) {
            $schema->removeProperty('id');
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
        $schema->setRequired($requiredFields);

        $searchableFields = array_merge($subSearchableFields, $model->getSearchableFields());

        $schema->setSearchable($searchableFields);

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
