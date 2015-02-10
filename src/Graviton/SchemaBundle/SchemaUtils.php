<?php
/**
 * Utils for generating schemas.
 *
 * PHP Version 5
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\SchemaBundle;

use Graviton\SchemaBundle\Document\Schema;

/**
 * Utils for generating schemas.
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
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

        // add pre translated fields
        $translatableFields = array_merge($translatableFields, $model->getPreTranslatedFields());

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

        foreach ($meta->getFieldNames() as $field) {
            /**
             * [nue] here there was a dirty workaround rename uri to $ref..
             * this is wrong and led to an error on an entity that actually had a field named 'uri'
             * if somebody wants to expose a field 'uri' as $ref, please use the serializer for that.
             * that's for what he's there..
             */
            /*
            if ($field == 'uri') {
                $field = '$ref';
            }
            */

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
