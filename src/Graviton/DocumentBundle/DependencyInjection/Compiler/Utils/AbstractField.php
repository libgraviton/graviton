<?php
/**
 * AbstractField class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Base document field
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AbstractField
{
    /**
     * @var string
     */
    private $fieldName;
    /**
     * @var string
     */
    private $exposedName;
    /**
     * @var bool
     */
    private $readOnly;
    /**
     * @var bool
     */
    private $required;
    /**
     * @var bool
     */
    private $searchable;
    /**
     * @var bool
     */
    private $recordOriginException;

    /**
     * Constructor
     *
     * @param string $fieldName             Field name
     * @param string $exposedName           Exposed name
     * @param bool   $readOnly              Read only
     * @param bool   $required              Is required
     * @param bool   $searchable            Is searchable
     * @param bool   $recordOriginException Is an exception to record origin
     */
    public function __construct($fieldName, $exposedName, $readOnly, $required, $searchable, $recordOriginException)
    {
        $this->fieldName = $fieldName;
        $this->exposedName = $exposedName;
        $this->readOnly = $readOnly;
        $this->required = $required;
        $this->searchable = $searchable;
        $this->recordOriginException = $recordOriginException;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get exposed name
     *
     * @return string
     */
    public function getExposedName()
    {
        return $this->exposedName;
    }

    /**
     * Is read only
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * @param boolean $searchable Is searchable
     *
     * @return void
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
    }

    /**
     * get RecordOriginException
     *
     * @return boolean RecordOriginException
     */
    public function isRecordOriginException()
    {
        return $this->recordOriginException;
    }

    /**
     * set RecordOriginException
     *
     * @param boolean $recordOriginException recordOriginException
     *
     * @return void
     */
    public function setRecordOriginException($recordOriginException)
    {
        $this->recordOriginException = $recordOriginException;
    }
}
