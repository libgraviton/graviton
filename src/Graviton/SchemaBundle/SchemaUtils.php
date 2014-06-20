<?php

namespace Graviton\SchemaBundle;

use Graviton\SchemaBundle\Document\Schema;

/**
 * Utils for generating schemas.
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SchemaUtils
{
    /**
     * return the schema for a given route
     *
     * @param string $modelName name of mode to generate schema for
     * @param object $model     model to generate schema for
     *
     * @return Schema
     */
    public static function getModelSchema($modelName, $model)
    {
        // build up schema data
        $schema = new Schema;
        $schema->setTitle(ucfirst($modelName));
        $schema->setDescription($model->getDescription());
        $schema->setType('object');

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

        foreach ($meta->getFieldNames() as $field) {
            $property = new Schema();
            $property->setType($meta->getTypeOfField($field));
            $property->setTitle($model->getTitleOfField($field));
            $property->setDescription($model->getDescriptionOfField($field));

            $schema->addProperty($field, $property);
        }
        $schema->setRequired($model->getRequiredFields());

        return $schema;
    }

    /**
     * get schema for an array of models
     *
     * @param string $modelName name of model
     * @param object $model     model
     *
     * @return \stdClass
     */
    public static function getCollectionSchema($modelName, $model)
    {
        $collectionSchema = new Schema;
        $collectionSchema->setTitle(sprintf('Array of %s objects', $modelName));
        $collectionSchema->setType('array');
        $collectionSchema->setItems(self::getModelSchema($modelName, $model));

        return $collectionSchema;
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
