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
 * @license  https://opensource.org/licenses/MIT MIT License
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
     *
     * @param string $jsonSchemaPath json schema path
     */
    public function __construct(string $jsonSchemaPath)
    {
        if (!file_exists($jsonSchemaPath)) {
            throw new \LogicException('Please create and pass a json schema file ' . $jsonSchemaPath);
        }

        $this->schema = \json_decode(file_get_contents($jsonSchemaPath));

        if (is_null($this->schema)) {
            throw new \LogicException('The file ' . $jsonSchemaPath . ' doe not contain valid json');
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
     * get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->schema->title;
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
     * get recordOriginModifiable
     *
     * @return bool
     */
    public function getRecordOriginModifiable()
    {
        if (isset($this->schema->recordOriginModifiable)) {
            return $this->schema->recordOriginModifiable;
        }
        return true;
    }

    /**
     * get isVersioning
     *
     * @return bool
     */
    public function isVersioning()
    {
        $isVersioning = false;
        if (isset($this->schema->{'x-versioning'})) {
            $isVersioning = $this->schema->{'x-versioning'};
        }
        return $isVersioning;
    }

    /**
     * Returns the bare schema
     *
     * @return \stdClass Schema
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
        return $this->getSchemaField($field, 'title');
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
        return $this->getSchemaField($field, 'description');
    }

    /**
     * get groups for a given field
     *
     * @param string $field field name
     *
     * @return array<string> group names
     */
    public function getGroupsOfField($field)
    {
        return $this->getSchemaField($field, 'x-groups', []);
    }

    /**
     * get property model for embedded field
     *
     * @param string $mapping name of mapping class
     *
     * @return $this
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
     * @param string $variationName a variation that can alter which fields are required
     *
     * @return string[]
     */
    public function getRequiredFields($variationName = null)
    {
        if (is_null($variationName)) {
            return $this->schema->required;
        }

        /* compose required fields based on variation */
        $requiredFields = $this->getRequiredFields();
        foreach ($this->schema->properties as $fieldName => $fieldAttributes) {
            $onVariation = $this->getOnVariaton($fieldName);

            if (is_object($onVariation) && isset($onVariation->{$variationName}->required)) {
                $thisRequired = $onVariation->{$variationName}->required;

                if ($thisRequired === true) {
                    $requiredFields[] = $fieldName;
                }

                if ($thisRequired === false) {
                    // see if its set
                    $fieldIndex = array_search($fieldName, $requiredFields);
                    if ($fieldName !== false) {
                        unset($requiredFields[$fieldIndex]);
                    }
                }
            }
        }

        return array_values(
            array_unique(
                $requiredFields
            )
        );
    }

    /**
     * get a collection of service names that can extref refer to
     *
     * @param string $field field name
     *
     * @return array
     */
    public function getRefCollectionOfField($field)
    {
        return $this->getSchemaField($field, 'collection', array());
    }

    /**
     * get readOnly flag for a given field
     *
     * @param string $field field name
     *
     * @return boolean the readOnly flag
     */
    public function getReadOnlyOfField($field)
    {
        return $this->getSchemaField($field, 'readOnly', false);
    }

    /**
     * get readOnly flag for a given field
     *
     * @param string $field field name
     *
     * @return boolean the readOnly flag
     */
    public function getRecordOriginExceptionOfField($field)
    {
        return $this->getSchemaField($field, 'recordOriginException', false);
    }

    /**
     * get searchable flag for a given field, weight based.
     *
     * @param string $field field name
     *
     * @return integer the searchable flag
     */
    public function getSearchableOfField($field)
    {
        return (int) $this->getSchemaField($field, 'searchable', 0);
    }

    /**
     * Gets the defined document class in shortform from schema
     *
     * @return string|false either the document class or false it not given
     */
    public function getDocumentClass()
    {
        $documentClass = false;
        if (isset($this->schema->{'x-documentClass'})) {
            $documentClass = $this->schema->{'x-documentClass'};
        }
        return $documentClass;
    }

    /**
     * Get defined constraints on this field (if any)
     *
     * @param string $field field that we get constraints spec from
     *
     * @return object
     */
    public function getConstraints($field)
    {
        return $this->getSchemaField($field, 'x-constraints', false);
    }

    /**
     * Get defined pattern on this field (if any)
     *
     * @param string $field field that we get constraints spec from
     *
     * @return object
     */
    public function getValuePattern($field)
    {
        return $this->getSchemaField($field, 'pattern', null);
    }


    /**
     * get schema field value
     *
     * @param string $field         field name
     * @param string $property      property name
     * @param mixed  $fallbackValue fallback value if property isn't set
     *
     * @return mixed
     */
    private function getSchemaField($field, $property, $fallbackValue = '')
    {
        if (isset($this->schema->properties->$field->$property)) {
            $fallbackValue = $this->schema->properties->$field->$property;
        }

        return $fallbackValue;
    }

    /**
     * get searchable fields for this object
     *
     * @return string[]
     */
    public function getSearchableFields()
    {
        if (!empty($this->schema->searchable)) {
            return $this->schema->searchable;
        }

        return [];
    }

    /**
     * get solr information array
     *
     * @return array
     */
    public function getSolrInformation()
    {
        if (isset($this->schema->solr)) {
            return $this->schema->solr;
        }
        return [];
    }
}
