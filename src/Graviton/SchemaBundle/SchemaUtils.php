<?php
/**
 * Utils for generating schemas.
 */

namespace Graviton\SchemaBundle;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
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
     * get schema for an array of models
     *
     * @param string   $modelName          name of model
     * @param object   $model              model
     * @param string[] $translatableFields fields that get translated on the fly
     * @param string[] $languages          languages
     *
     * @return Schema
     */
    public static function getCollectionSchema($modelName, $model, $translatableFields, $languages)
    {
        $collectionSchema = new Schema;
        $collectionSchema->setTitle(sprintf('Array of %s objects', $modelName));
        $collectionSchema->setType('array');
        $collectionSchema->setItems(self::getModelSchema($modelName, $model, $translatableFields, $languages));

        return $collectionSchema;
    }

    /**
     * return the schema for a given route
     *
     * @param string   $modelName          name of mode to generate schema for
     * @param object   $model              model to generate schema for
     * @param string[] $translatableFields fields that get translated on the fly
     * @param string[] $languages          languages
     *
     * @return Schema
     */
    public static function getModelSchema($modelName, $model, $translatableFields, $languages)
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
                $translatableFields = $documentClass->getTranslatableFields();
            }
        }

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
                $property->setItems(self::getModelSchema($field, $propertyModel, $translatableFields, $languages));
                $property->setType('array');
            }

            if ($meta->getTypeOfField($field) === 'one') {
                $propertyModel = $model->manyPropertyModelForTarget($meta->getAssociationTargetClass($field));
                $property = self::getModelSchema($field, $propertyModel, $translatableFields, $languages);
            }

            if (in_array($field, $translatableFields)) {
                $property = self::makeTranslatable($property, $languages);
            }

            $schema->addProperty($field, $property);
        }

        $schema->setRequired($model->getRequiredFields());

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
    public static function makeTranslatable(Schema $property, $languages)
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
