<?php

namespace Graviton\SchemaBundle;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function getModelSchema($modelName, $model)
    {
        // build up schema data
        $schema = new \stdClass;
        $schema->title = ucfirst($modelName);
        $schema->description = $model->getDescription();
        $schema->type = 'object';
        $schema->properties = new \stdClass;

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

        foreach ($meta->getFieldNames() as $field) {
            $schema->properties->$field = new \stdClass;
            $schema->properties->$field->type = $meta->getTypeOfField($field);
            $schema->properties->$field->description = $model->getDescriptionOfField($field);
        }
        $schema->required = $model->getRequiredFields();

        return $schema;
    }
}
