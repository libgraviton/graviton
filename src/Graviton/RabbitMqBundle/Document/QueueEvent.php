<?php
/**
 * queueevent
 */

namespace Graviton\RabbitMqBundle\Document;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * Graviton\RabbitMqBundle\Document\QueueEvent
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class QueueEvent implements TranslatableDocumentInterface
{

    /**
     * @var string $event
     */
    public $event;

    /**
     * @var string $coreId
     */
    public $coreId;

    /**
     * @var \stdClass $document
     */
    public $document;

    /**
     * @var \stdClass $status
     */
    public $status;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->document = new \stdClass();
        $this->status = new \stdClass();
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
    public function getCoreId()
    {
        return $this->coreId;
    }

    /**
     * Set user Core Id
     *
     * @param string $coreId User Core Id
     *
     * @return self
     */
    public function setCoreId($coreId)
    {
        $this->coreId = $coreId;

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
     * return translatable field names
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return [];
    }

    /**
     * return pretranslated field names
     *
     * @return string[]
     */
    public function getPreTranslatedFields()
    {
        return [];
    }
}
