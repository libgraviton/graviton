<?php
/**
 * Custom Model Document listener
 */
namespace Graviton\AuditTrackingBundle\Listener;

use Graviton\RestBundle\Event\ModelEvent;
use Graviton\AuditTrackingBundle\Manager\ActivityManager;

/**
 * Class DBActivityListener
 * @package Graviton\AuditTrackingBundle\Listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentModelListener
{
    /** @var ActivityManager */
    private $manager;

    /**
     * DBActivityListener constructor.
     * @param ActivityManager $activityManager Business logic
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->manager = $activityManager;
    }

    /**
     * Updating a Model
     * @param ModelEvent $event Mongo.odm event argument
     * @return void
     */
    public function modelUpdate(ModelEvent $event)
    {
        $this->manager->registerDocumentModelEvent($event);
    }

    /**
     * Insert a Model
     * @param ModelEvent $event Mongo.odm event argument
     * @return void
     */
    public function modelInsert(ModelEvent $event)
    {
        $this->manager->registerDocumentModelEvent($event);
    }

    /**
     * Insert a Model
     * @param ModelEvent $event Mongo.odm event argument
     * @return void
     */
    public function modelDelete(ModelEvent $event)
    {
        $this->manager->registerDocumentModelEvent($event);
    }
}
