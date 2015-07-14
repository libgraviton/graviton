<?php
/**
 * Utils for generating schemas.
 */

namespace Graviton\SchemaBundle;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Graviton\SchemaBundle\Document\Schema;

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
     * Constructor
     *
     * @param LanguageRepository $languageRepository repository
     */
    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * get schema for an array of models
     *
     * @param string $modelName name of model
     * @param object $model     model
     *
     * @return Schema
     */
    public function getCollectionSchema($modelName, $model)
    {
        $collectionSchema = new Schema;
        $collectionSchema->setTitle(sprintf('Array of %s objects', $modelName));
        $collectionSchema->setType('array');
        $collectionSchema->setItems(self::getModelSchema($modelName, $model));

        return $collectionSchema;
    }

    /**
     * return the schema for a given route
     *
     * @param string $modelName name of mode to generate schema for
     * @param object $model     model to generate schema for
     *
     * @return Schema
     */
    public function getModelSchema($modelName, $model)
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
        $entityName = $repo->getClassName();
        $translatableFields = array();
        if (class_exists($entityName)) {
            $documentClass = new $entityName();
            if ($documentClass instanceof TranslatableDocumentInterface) {
                $translatableFields = array_merge(
                    $documentClass->getTranslatableFields(),
                    $documentClass->getPreTranslatedFields()
                );
            }
        }

        $languages = array_map(
            function ($language) {
                return $language->getId();
            },
            $this->languageRepository->findAll()
        );

        foreach ($meta->getFieldNames() as $field) {
            // don't describe deletedDate in schema..
            if ($field == 'deletedDate') {
                continue;
            }

            $property = new Schema();
            $property->setTitle($model->getTitleOfField($field));
            $property->setDescription($model->getDescriptionOfField($field));

            $property->setType($meta->getTypeOfField($field));

            if ($meta->getTypeOfField($field) === 'many') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));
                $property->setItems(self::getModelSchema($field, $propertyModel));
                $property->setType('array');
            }

            if ($meta->getTypeOfField($field) === 'one') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));
                $property = self::getModelSchema($field, $propertyModel);
            }

            if (in_array($field, $translatableFields)) {
                $property = self::makeTranslatable($property, $languages);
            }

            if ($meta->getTypeOfField($field) === 'extref' && substr($field, 0, 1) !== '$') {
                $field = '$' . $field;
            }

            $schema->addProperty($field, $property);
        }

        if ($meta->isEmbeddedDocument && !in_array('id', $model->getRequiredFields())) {
            $schema->removeProperty('id');
        }

        $requiredFields = $model->getRequiredFields();
        foreach ($requiredFields as $index => $requiredField) {
            if ($requiredField === 'ref') {
                $requiredFields[$index] = '$' . $requiredFields[$index];
            }
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
