<?php
/**
 * RuntimeDefinition
 */

namespace Graviton\RestBundle\Model;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RuntimeDefinition
{

    /**
     * @var string[]
     */
    private array $recordOriginExceptionFields = [];

    /**
     * @var string[]
     */
    private array $readOnlyFields = [];

    /**
     * @var string[]
     */
    private array $incrementalDateFields = [];

    /**
     * @var array[string, string]
     */
    private array $restEventNames = [];

    /**
     * @var bool
     */
    private bool $isVersioned = false;

    /**
     * get fields
     *
     * @return string[] fields
     */
    public function getRecordOriginExceptionFields(): array
    {
        return $this->recordOriginExceptionFields;
    }

    /**
     * set fields
     *
     * @param array $recordOriginExceptionFields fields
     *
     * @return void fields
     */
    public function setRecordOriginExceptionFields(array $recordOriginExceptionFields): void
    {
        $this->recordOriginExceptionFields = $recordOriginExceptionFields;
    }

    /**
     * get fields
     *
     * @return array fields
     */
    public function getReadOnlyFields(): array
    {
        return $this->readOnlyFields;
    }

    /**
     * set fields
     *
     * @param array $readOnlyFields fields
     *
     * @return void
     */
    public function setReadOnlyFields(array $readOnlyFields): void
    {
        $this->readOnlyFields = $readOnlyFields;
    }

    /**
     * @return array
     */
    public function getIncrementalDateFields(): array
    {
        return $this->incrementalDateFields;
    }

    /**
     * @param array $incrementalDateFields
     */
    public function setIncrementalDateFields(array $incrementalDateFields): void
    {
        $this->incrementalDateFields = $incrementalDateFields;
    }

    /**
     * @return bool
     */
    public function isVersioned(): bool
    {
        return $this->isVersioned;
    }

    /**
     * @param bool $isVersioned
     */
    public function setIsVersioned(bool $isVersioned): void
    {
        $this->isVersioned = $isVersioned;
    }

    /**
     * @return array
     */
    public function getRestEventNames(): array
    {
        return $this->restEventNames;
    }

    /**
     * @param array $restEventNames
     */
    public function setRestEventNames(array $restEventNames): void
    {
        $this->restEventNames = $restEventNames;
    }
}
