<?php
/**
 * monolog processor that adds a requestid to the record
 */

namespace Graviton\LogBundle\Monolog\Processor;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RequestIdProcessor
{

    /**
     * @var string
     */
    private $requestId;

    /**
     * processes the record and adds a random request id
     *
     * @param array $record record
     *
     * @return array new record
     */
    public function processRecord(array $record)
    {
        if (is_null($this->requestId)) {
            $this->requestId = uniqid('', true);
        }

        $record['extra']['requestId'] = $this->requestId;

        return $record;
    }
}
