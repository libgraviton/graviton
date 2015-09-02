<?php

namespace Graviton\RabbitMqBundle\Document;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * Graviton\RabbitMqBundle\Document\QueueEvent
 *
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class QueueEvent implements TranslatableDocumentInterface {
    /**
     * @var MongoId $id
     */
    public $id;

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
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param mixed $id id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get className
     *
     * @return string $className
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
     * @return string $recordId
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
