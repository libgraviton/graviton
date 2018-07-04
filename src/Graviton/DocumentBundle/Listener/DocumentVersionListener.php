<?php
/**
 * Custom Model Document Version listener
 */

namespace Graviton\DocumentBundle\Listener;

use Graviton\RestBundle\Event\ModelEvent;
use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\SchemaBundle\Constraint\VersionServiceConstraint;

/**
 * Class DocumentVersionListener
 * @package Graviton\DocumentBundle\Listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentVersionListener
{
    /** @var DocumentManager Document manager */
    private $documentManager;

    /**
     * @var VersionServiceConstraint
     */
    private $constraint;

    /**
     * constructor.
     *
     * @param DocumentManager          $documentManager Db Connection document manager
     * @param VersionServiceConstraint $constraint      constraint
     */
    public function __construct(DocumentManager $documentManager, VersionServiceConstraint $constraint)
    {
        $this->documentManager = $documentManager;
        $this->constraint = $constraint;
    }

    /**
     * Updating a Model
     * @param ModelEvent $event Mongo.odm event argument
     * @return void
     */
    public function modelUpdate(ModelEvent $event)
    {
        $this->updateCounter($event, 'update');
    }
    /**
     * Insert a Model
     * @param ModelEvent $event Mongo.odm event argument
     * @return void
     */
    public function modelInsert(ModelEvent $event)
    {
        $this->updateCounter($event, 'insert');
    }

    /**
     * Update Counter for all new saved items
     *
     * @param ModelEvent $event  Object event
     * @param string     $action What is to be done
     * @return void
     */
    private function updateCounter(ModelEvent $event, $action)
    {
        if (!$this->constraint->isVersioningService()) {
            return;
        }

        $qb = $this->documentManager->createQueryBuilder($event->getCollectionClass());
        if ('update' == $action) {
            $qb->findAndUpdate()
                ->field('id')->equals($event->getCollectionId())
                ->field(VersionServiceConstraint::FIELD_NAME)->inc(1)
                ->getQuery()->execute();
        } else {
            $qb->findAndUpdate()
                ->field('id')->equals($event->getCollectionId())
                ->field(VersionServiceConstraint::FIELD_NAME)->set(1)
                ->getQuery()->execute();
        }
    }
}
