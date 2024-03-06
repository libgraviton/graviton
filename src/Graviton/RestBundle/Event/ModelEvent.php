<?php
/**
 * Event for Model collection changes
 */

namespace Graviton\RestBundle\Event;

use Graviton\RestBundle\Model\DocumentModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is passed to graviton.rest.event listeners
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ModelEvent extends Event
{
    /** EVENT: Insert a new Document */
    const string MODEL_EVENT_INSERT = 'document.model.event.insert';

    /** EVENT: Update a new Document */
    const string MODEL_EVENT_UPDATE = 'document.model.event.update';

    /** EVENT: Delete a new Document */
    const string MODEL_EVENT_DELETE = 'document.model.event.delete';

    public function __construct(
        protected readonly string $eventName,
        protected readonly string $recordId,
        protected readonly DocumentModel $documentModel,
        protected readonly ?Request $request = null
    ) {
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function getRecordId(): string
    {
        return $this->recordId;
    }

    /**
     * @return DocumentModel
     */
    public function getDocumentModel(): DocumentModel
    {
        return $this->documentModel;
    }

    /**
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
