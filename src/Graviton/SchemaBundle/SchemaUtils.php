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
     * Constructor
     *
     * @param RepositoryFactory  $repositoryFactory    Create repos from model class names
     * @param LanguageRepository $languageRepository   repository
     * @param RouterInterface    $router               router
     * @param array              $extrefServiceMapping Extref service mapping
     * @param array              $documentFieldNames   Document field names
     * @param string             $defaultLocale        Default Language
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        LanguageRepository $languageRepository,
        RouterInterface $router,
        array $extrefServiceMapping,
        array $documentFieldNames,
        $defaultLocale
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->languageRepository = $languageRepository;
        $this->router = $router;
        $this->extrefServiceMapping = $extrefServiceMapping;
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
        $schema->setTitle(ucfirst($modelName));
        $schema->setDescription($model->getDescription());
        $schema->setType('object');

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

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

        foreach ($meta->getFieldNames() as $field) {
            // don't describe hidden fields
            if (!isset($documentFieldNames[$field])) {
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
            }

            if ($meta->getTypeOfField($field) === 'one') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));
                $property = $this->getModelSchema($field, $propertyModel, $online);
            }

            if (in_array($field, $translatableFields, true)) {
                $property = $this->makeTranslatable($property, $languages);
            }

            if ($meta->getTypeOfField($field) === 'extref') {
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
            }
            $schema->addProperty($documentFieldNames[$field], $property);
        }

        if ($meta->isEmbeddedDocument && !in_array('id', $model->getRequiredFields())) {
            $schema->removeProperty('id');
        }

        $requiredFields = [];
        foreach ($model->getRequiredFields() as $field) {
            // don't describe hidden fields
            if (!isset($documentFieldNames[$field])) {
                continue;
            }

            $requiredFields[] = $documentFieldNames[$field];
        }
        $schema->setRequired($requiredFields);

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
}
