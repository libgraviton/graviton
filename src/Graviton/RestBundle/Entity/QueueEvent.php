<?php
/**
 * queueevent
 */

namespace Graviton\RestBundle\Entity;

/**
 * Graviton\RabbitMqBundle\Document\QueueEvent
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class QueueEvent
{

    /**
     * @var string $event
     */
    public $event;

    /**
     * @var string $coreUserId
     */
    public $coreUserId;

    /**
     * @var \stdClass $document
     */
    public $document;

    /**
     * @var \stdClass $status
     */
    public $status;

    /**
     * @var array
     */
    public $transientHeaders;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->document = new \stdClass();
        $this->status = new \stdClass();
        $this->transientHeaders = new \ArrayObject();
    }

    /**
     * Get event
     *
     * @return string $event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event
     *
     * @param string $event value for event
     *
     * @return self
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get user Core Id
     *
     * @return string
     */
    public function getCoreUserId()
    {
        return $this->coreUserId;
    }

    /**
     * Set user Core Id
     *
     * @param string $coreUserId User Core Id
     *
     * @return self
     */
    public function setCoreUserId($coreUserId)
    {
        $this->coreUserId = $coreUserId;

        return $this;
    }

    /**
     * Get documentUrl
     *
     * @return string document url
     */
    public function getDocumenturl()
    {
        return $this->document->{'$ref'};
    }

    /**
     * Set documentUrl
     *
     * @param string $documentUrl value for documentUrl
     *
     * @return self
     */
    public function setDocumenturl($documentUrl)
    {
        $this->document->{'$ref'} = $documentUrl;
        return $this;
    }

    /**
     * Get statusUrl
     *
     * @return string document url
     */
    public function getStatusurl()
    {
        return $this->status->{'$ref'};
    }

    /**
     * Set statusUrl
     *
     * @param string $statusUrl value for statusUrl
     *
     * @return self
     */
    public function setStatusurl($statusUrl)
    {
        $this->status->{'$ref'} = $statusUrl;
        return $this;
    }

    /**
     * get headers
     *
     * @return array headers
     */
    public function getTransientHeaders(): array
    {
        return $this->transientHeaders;
    }

    /**
     * set headers
     *
     * @param array $transientHeaders headers
     *
     * @return void
     */
    public function setTransientHeaders(array $transientHeaders): void
    {
        $this->transientHeaders = $transientHeaders;
    }

    /**
     * add header
     *
     * @param string $headerName  header name
     * @param string $headerValue header value
     *
     * @return void
     */
    public function addTransientHeader(string $headerName, string $headerValue): void
    {
        $this->transientHeaders[$headerName] = $headerValue;
    }
}
