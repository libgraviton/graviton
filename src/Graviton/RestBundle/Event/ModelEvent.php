<?php
/**
 * Event for Model collection changes
 */

namespace Graviton\RestBundle\Event;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is passed to graviton.rest.event listeners
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ModelEvent extends Event
{
    /** EVENT: Insert a new Document */
    const MODEL_EVENT_INSERT = 'document.model.event.insert';

    /** EVENT: Update a new Document */
    const MODEL_EVENT_UPDATE = 'document.model.event.update';

    /** EVENT: Delete a new Document */
    const MODEL_EVENT_DELETE = 'document.model.event.delete';

    /**
     * @var array list with event names
     */
    private $availableEvents = [
        self::MODEL_EVENT_INSERT => 'insert',
        self::MODEL_EVENT_UPDATE => 'update',
        self::MODEL_EVENT_DELETE => 'delete'
    ];

    /**
     * Containing operation executed
     * update or insert
     *
     * @var string
     */
    private $action;

    /**
     * Containing the ID of the Model on which the change happened
     *
     * @var string
     */
    private $collectionId;

    /**
     * Containing the name of the Collection affected
     *
     * @var string
     */
    private $collectionName;

    /**
     * Containing the name of the Collection affected
     *
     * @var string
     */
    private $collectionClass;

    /**
     * Containing the changed object collection
     *
     * @var Object
     */
    private $collection;

    /**
     * Set the event ACTION name based on the fired name
     *
     * @param string $dispatchName the CONST value for dispatch names
     *
     * @return string
     * @throws Exception
     */
    public function setActionByDispatchName($dispatchName)
    {
        if (array_key_exists($dispatchName, $this->availableEvents)) {
            $this->action = $this->availableEvents[$dispatchName];
        } else {
            throw new \RuntimeException('Document Model event dispatch type not found: ' . $dispatchName);
        }
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string Collection ID
     */
    public function getCollectionId()
    {
        return $this->collectionId;
    }

    /**
     * @param string $collectionId Collection ID
     * @return void
     */
    public function setCollectionId($collectionId)
    {
        $this->collectionId = $collectionId;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName Name to Be
     * @return void
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @return string
     */
    public function getCollectionClass()
    {
        return $this->collectionClass;
    }

    /**
     * @param string $collectionClass Collection Class
     * @return void
     */
    public function setCollectionClass($collectionClass)
    {
        $this->collectionClass = $collectionClass;
    }

    /**
     * @return Object
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param Object $collection Collection Object
     * @return void
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }
}
