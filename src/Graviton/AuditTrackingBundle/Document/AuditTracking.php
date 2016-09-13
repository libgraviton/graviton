<?php
/**
 * document class for Graviton\AuditTrackingBundle\Document\ActivityLog
 */

namespace Graviton\AuditTrackingBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuditTracking
{
    /**
     * @var mixed $id
     */
    protected $id;
    
    /**
     * @var string $thread
     */
    protected $thread;

    /**
     * @var string $username
     */
    protected $username;

    /**
     * @var string $action
     */
    protected $action;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var string $location
     */
    protected $location;

    /**
     * @var ArrayCollection $data
     */
    protected $data;

    /**
     * @ string $collectionName
     */
    protected $collectionId;

    /**
     * @ string $collectionName
     */
    protected $collectionName;

    /**
     * @var \datetime $createdAt
     */
    protected $createdAt;

    /**
     * constructor
     *
     * @return self
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param string $thread string id to UUID thread for user
     * @return void
     */
    public function setThread($thread)
    {
        $this->thread = $thread;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username Current user name
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action what happened
     * @return void
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type type of event
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location where did the action happen
     * @return void
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return object
     */
    public function getData()
    {
        return empty($this->data) ? null : $this->data;
    }

    /**
     * @param Object $data additional information
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getCollectionId()
    {
        return $this->collectionId;
    }

    /**
     * @param mixed $collectionId Collection ID
     * @return void
     */
    public function setCollectionId($collectionId)
    {
        $this->collectionId = $collectionId;
    }

    /**
     * @return mixed
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @param mixed $collectionName Collection name
     * @return void
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @return \datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \datetime $createdAt when the event took place
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
