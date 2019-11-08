<?php
/**
 * event that fires before we persist an entity in rest context
 */

namespace Graviton\RestBundle\Event;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EntityPrePersistEvent extends Event
{

    /**
     * @var string
     */
    public const NAME = 'document.model.event.entity.pre_persist';

    /**
     * @var object
     */
    private $entity;

    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * gets entity
     *
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * set entity
     *
     * @param object $entity entity
     *
     * @return void
     */
    public function setEntity(object $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return DocumentRepository repository
     */
    public function getRepository(): DocumentRepository
    {
        return $this->repository;
    }

    /**
     * @param DocumentRepository $repository repository
     *
     * @return void
     */
    public function setRepository(DocumentRepository $repository): void
    {
        $this->repository = $repository;
    }
}
