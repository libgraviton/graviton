<?php
/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 */

namespace Graviton\SchemaBundle\Model;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
     */
    public function __construct()
    {
        list(, $bundle, , $model) = explode('\\', get_called_class());
        $file = __DIR__ . '/../../' . $bundle . '/Resources/config/schema/' . $model . '.json';

        if (!file_exists($file)) {
            // fallback try on model property (this should be available on some generated classes)
            if (isset($this->_modelPath)) {
                // try to find schema.json relative to the model involved..
                $file = dirname($this->_modelPath) . '/../Resources/config/schema/' . $model . '.json';
            }

            if (!file_exists($file)) {
                throw new \LogicException('Please create the schema file ' . $file);
            }
        }

        $this->schema = \json_decode(file_get_contents($file));

        if (is_null($this->schema)) {
            throw new \LogicException('The file ' . $file . ' doe not contain valid json');
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
     * @return string Description
     */
    public function getDescription()
    {
        return $this->schema->description;
    }

    /**
     * Returns the bare schema
     *
     * @return stdClass Schema
     */
    public function getSchema()
    {
        return $this->schema;
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
        $ret = '';
        if (isset($this->schema->properties->$field->title)) {
            $ret = $this->schema->properties->$field->title;
        }
        return $ret;
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
        $ret = '';
        if (isset($this->schema->properties->$field->description)) {
            $ret = $this->schema->properties->$field->description;
        }
        return $ret;
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
