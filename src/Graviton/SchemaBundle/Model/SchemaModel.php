<?php

namespace Graviton\SchemaBundle\Model;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @category SchemaBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SchemaModel
{
    /**
     * object
     */
    private $schema;

    /**
     * load some schema info for the model
     *
     * @return SchemaModel
     */
    public function __construct()
    {
        list(, $bundle, ,$model) = explode('\\', get_called_class());
        $file = __DIR__.'/../../'.$bundle.'/Resources/config/schema/'.$model.'.json';

        if (!file_exists($file)) {
            throw new \LogicException('Please create the schema file '.$file);
        }

        $this->schema = \json_decode(file_get_contents($file));
    }

    /**
     * get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->schema->description;
    }

    /**
     * get title for a given field
     *
     * @param string $field field name
     *
     * @return string
     */
    public function getTitleOfField($field)
    {
        return $this->schema->properties->$field->title;
    }

    /**
     * get description for a given field
     *
     * @param string $field field name
     *
     * @return string
     */
    public function getDescriptionOfField($field)
    {
        return $this->schema->properties->$field->description;
    }

    /**
     * get required fields for this object
     *
     * @return string[]
     */
    public function getRequiredFields()
    {
        return $this->schema->required;
    }
}
