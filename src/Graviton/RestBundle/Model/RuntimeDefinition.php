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

    private bool $isRecordOriginException = false;

    public function isRecordOriginException(): bool
    {
        return $this->isRecordOriginException;
    }

    public function setIsRecordOriginException(bool $isRecordOriginException): void
    {
        $this->isRecordOriginException = $isRecordOriginException;
    }

}
