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
     * @var string $className
     */
    public $className;

    /**
     * @var string $recordId
     */
    public $recordId;

    /**
     * @var string $event
     */
    public $event;

    /**
     * @var string $publicUrl
     */
    public $publicUrl;

    /**
     * @var string $statusUrl
     */
    public $statusUrl;

    /**
     * @var string $routingKey
     */
    public $routingKey;

    /**
     * Get className
     *
     * @return string $className classname
     */
    public function getClassname()
    {
        return $this->className;
    }

    /**
     * Set className
     *
     * @param string $className value for className
     *
     * @return self
     */
    public function setClassname($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get recordId
     *
     * @return string $recordId recordId
     */
    public function getRecordid()
    {
        return $this->recordId;
    }

    /**
     * Set recordId
     *
     * @param string $recordId value for recordId
     *
     * @return self
     */
    public function setRecordid($recordId)
    {
        $this->recordId = $recordId;

        return $this;
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
     * Get publicUrl
     *
     * @return string $publicUrl
     */
    public function getPublicurl()
    {
        return $this->publicUrl;
    }

    /**
     * Set publicUrl
     *
     * @param string $publicUrl value for publicUrl
     *
     * @return self
     */
    public function setPublicurl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * Get statusUrl
     *
     * @return string $statusUrl
     */
    public function getStatusurl()
    {
        return $this->statusUrl;
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
        $this->statusUrl = $statusUrl;

        return $this;
    }

    /**
     * Get routingKey
     *
     * @return string $routingKey
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * Set routingKey
     *
     * @param string $routingKey routingKey
     *
     * @return self
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;

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
