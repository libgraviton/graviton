<?php
/**
 * null model
 */

namespace Graviton\SecurityBundle\User\Model;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Model\ModelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NullModel
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class NullModel extends DocumentModel
{

    /**
     * Find a single record by id
     *
     * @param string $documentId id
     * @param bool   $forceClear force
     *
     * @return Object
     */
    public function find($documentId, $forceClear = false)
    {
        return;
    }

    /**
     * returns the schema
     *
     * @return array schema
     */
    public function getSchema()
    {
        return [];
    }

    /**
     * title
     *
     * @return string title
     */
    public function getTitle()
    {
        return "";
    }

    /**
     * title
     *
     * @param string $field field
     *
     * @return string title
     */
    public function getTitleOfField($field)
    {
        return "";
    }

    /**
     * Description
     *
     * @return string Description
     */
    public function getDescription()
    {
        return "";
    }

    /**
     * Description
     *
     * @param string $field field
     *
     * @return string Description
     */
    public function getDescriptionOfField($field)
    {
        return "";
    }

    /**
     * GroupsOfField
     *
     * @param string $field field
     *
     * @return array GroupsOfField
     */
    public function getGroupsOfField($field)
    {
        return [];
    }

    /**
     * ReadOnlyOfField
     *
     * @param string $field field
     *
     * @return bool ReadOnlyOfField
     */
    public function getReadOnlyOfField($field)
    {
        return true;
    }

    /**
     * DocumentClass
     *
     * @return string DocumentClass
     */
    public function getDocumentClass()
    {
        return \stdClass::class;
    }

    /**
     * RecordOriginModifiable
     *
     * @return bool RecordOriginModifiable
     */
    public function getRecordOriginModifiable()
    {
        return true;
    }

    /**
     * Versioning
     *
     * @return bool Versioning
     */
    public function isVersioning()
    {
        return false;
    }

    /**
     * pattern
     *
     * @param string $field field
     *
     * @return string pattern
     */
    public function getValuePattern($field)
    {
        return null;
    }

    /**
     * Variations
     *
     * @return array Variations
     */
    public function getVariations()
    {
        return [];
    }

    /**
     * OnVariaton
     *
     * @param string $field
     * @return array OnVariaton
     */
    public function getOnVariaton($field)
    {
        return [];
    }

    /**
     * SolrInformation
     *
     * @return array SolrInformation
     */
    public function getSolrInformation()
    {
        return [];
    }

    /**
     * RequiredFields
     *
     * @param null $variationName
     * @return array RequiredFields
     */
    public function getRequiredFields($variationName = null)
    {
        return [];
    }

    /**
     * Constraints
     *
     * @param string $field
     * @return array Constraints
     */
    public function getConstraints($field)
    {
        return [];
    }

    /**
     * SearchableFields
     *
     * @return array SearchableFields
     */
    public function getSearchableFields()
    {
        return [];
    }

    /**
     * RecordOriginExceptionOfField
     *
     * @param string $field field
     *
     * @return bool RecordOriginExceptionOfField
     */
    public function getRecordOriginExceptionOfField($field)
    {
        return false;
    }

    /**
     * Find all records
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return Object[]
     */
    public function findAll(Request $request)
    {
        return [];
    }

    /**
     * Insert a new Record
     *
     * @param object $entity       entity to insert
     * @param bool   $returnEntity true to return entity
     * @param bool   $doFlush      if we should flush or not after insert
     *
     * @return Object
     */
    public function insertRecord($entity, $returnEntity = true, $doFlush = true)
    {
        return $this->find($entity->getId());
    }

    /**
     * Update an existing entity
     *
     * @param string $documentId   id of entity to update
     * @param Object $entity       new entity
     * @param bool   $returnEntity true to return entity
     *
     * @return Object
     */
    public function updateRecord($documentId, $entity, $returnEntity = true)
    {
        return;
    }

    /**
     * Delete a record by id
     *
     * @param Number $id Record-Id
     *
     * @return null|Object
     */
    public function deleteRecord($id)
    {
        return null;
    }

    /**
     * Get the name of entity class
     *
     * @return string
     */
    public function getEntityClass()
    {
        return '';
    }

    /**
     * Get the connection name
     *
     * @return string
     */
    public function getConnectionName()
    {
        return '';
    }
}
