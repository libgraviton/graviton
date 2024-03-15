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
     * @var string[]
     */
    private array $extRefFields = [];

    /**
     * @var array<string, string>
     */
    private array $exposeAsMap = [];

    /**
     * @var array[string, string]
     */
    private array $restEventNames = [];

    /**
     * @var bool
     */
    private bool $isVersioned = false;

    /**
     * @var bool
     */
    private bool $preferredReadFromSecondary = false;

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
     * getIncrementalDateFields
     *
     * @return array fields
     */
    public function getIncrementalDateFields(): array
    {
        return $this->incrementalDateFields;
    }

    /**
     * setIncrementalDateFields
     *
     * @param array $incrementalDateFields fields
     *
     * @return void
     */
    public function setIncrementalDateFields(array $incrementalDateFields): void
    {
        $this->incrementalDateFields = $incrementalDateFields;
    }

    /**
     * getExtRefFields
     *
     * @return array fields
     */
    public function getExtRefFields(): array
    {
        return $this->extRefFields;
    }

    /**
     * setExtRefFields
     *
     * @param array $extRefFields fields
     *
     * @return void
     */
    public function setExtRefFields(array $extRefFields): void
    {
        $this->extRefFields = $extRefFields;
    }

    /**
     * getExposeAsMap
     *
     * @return array map
     */
    public function getExposeAsMap(): array
    {
        return $this->exposeAsMap;
    }

    /**
     * setExposeAsMap
     *
     * @param array $exposeAsMap map
     *
     * @return void
     */
    public function setExposeAsMap(array $exposeAsMap): void
    {
        $this->exposeAsMap = $exposeAsMap;
    }

    /**
     * isVersioned
     *
     * @return bool yes or not
     */
    public function isVersioned(): bool
    {
        return $this->isVersioned;
    }

    /**
     * setIsVersioned
     *
     * @param bool $isVersioned if versioned
     *
     * @return void
     */
    public function setIsVersioned(bool $isVersioned): void
    {
        $this->isVersioned = $isVersioned;
    }

    /**
     * getRestEventNames
     *
     * @return array event names
     */
    public function getRestEventNames(): array
    {
        return $this->restEventNames;
    }

    /**
     * setRestEventNames
     *
     * @param array $restEventNames event names
     *
     * @return void
     */
    public function setRestEventNames(array $restEventNames): void
    {
        $this->restEventNames = $restEventNames;
    }

    /**
     * isPreferredReadFromSecondary
     *
     * @return bool yes or not
     */
    public function isPreferredReadFromSecondary(): bool
    {
        return $this->preferredReadFromSecondary;
    }

    /**
     * setPreferredReadFromSecondary
     *
     * @param bool $preferredReadFromSecondary secondary
     *
     * @return void
     */
    public function setPreferredReadFromSecondary(bool $preferredReadFromSecondary): void
    {
        $this->preferredReadFromSecondary = $preferredReadFromSecondary;
    }
}
