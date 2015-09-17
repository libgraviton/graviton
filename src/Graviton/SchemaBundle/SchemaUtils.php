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
     * Constructor
     *
     * @param LanguageRepository $languageRepository   repository
     * @param RouterInterface    $router               router
     * @param array              $extrefServiceMapping Extref service mapping
     * @param array              $eventMap             eventmap
     * @param array              $documentFieldNames   Document field names
     * @param string             $defaultLocale        Default Language
     */
    public function __construct(
        LanguageRepository $languageRepository,
        RouterInterface $router,
        array $extrefServiceMapping,
        array $eventMap,
        array $documentFieldNames,
        $defaultLocale
    ) {
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

        // exposed events..
        $classShortName = $documentReflection->getShortName();
        if (isset($this->eventMap[$classShortName])) {
            $schema->setEventNames($this->eventMap[$classShortName]['events']);
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
                $property->setItems($this->getModelSchema($field, $propertyModel, $online));
                $property->setType('array');
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
