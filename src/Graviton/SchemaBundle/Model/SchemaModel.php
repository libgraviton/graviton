<?php

namespace Graviton\SchemaBundle\Model;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @category SchemaBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SchemaModel implements ContainerAwareInterface
{
    /**
     * object
     */
    private $schema;

    /**
     * @var ContainerInterface
     */
    private $container;

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

        if (is_null($this->schema)) {
            throw new \LogicException('The file '.$file.' doe not contain valid json');
        }
    }

    /**
     * inject container
     *
     * @param ContainerInterface $container service container
     *
     * @return self
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
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
     * get property model for embedded field
     *
     * @param string $mapping name of mapping class
     *
     * @return self
     */
    public function manyPropertyModelForTarget($mapping)
    {
        // @todo refactor to get rid of container dependency (maybe remove from here)
        list($app, $bundle, , $document) = explode('\\', $mapping);
        $app = strtolower($app);
        $bundle = strtolower(substr($bundle, 0, -6));
        $document = strtolower($document);
        $propertyService = implode('.', array($app, $bundle, 'model', $document));
        $propertyModel = $this->container->get($propertyService);

        return $propertyModel;
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

    /**
     * get pretranslated fields for this object
     *
     * @return string[]
     */
    public function getPreTranslatedFields()
    {
        $return = array();
        if (isset($this->schema->pretranslated)) {
            $return = $this->schema->pretranslated;
        }

        return $return;
    }
}
