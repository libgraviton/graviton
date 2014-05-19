<?php

namespace Graviton\RestBundle\Model\Doctrine;

use Graviton\RestBundle\Model\ModelInterface;

class ODM implements ModelInterface
{
    public function find($id)
    {
        return $this->repository->find($id);
    }
    public function findAll()
    {
        return $this->repository->findAll();
    }
    public function insertRecord($entity)
    {
        $dm = $this->repository->getDocumentManager();
        $res = $dm->persist($entity);
        $dm->flush();
        return $this->find($entity->getId());
    }
    public function updateRecord($id, $entity)
    {
        $dm = $this->repository->getDocumentManager();
        $dm->persist($entity);
        $dm->flush();
        return $entity;
    }
    public function deleteRecord($id)
    {
        $dm = $this->repository->getDocumentManager();
        $entity = $this->find($id);

        $return = false;
        if ($entity) {
            $dm->remove($entity);
            $return = true;
        }
        return $return;
    }
    public function getEntityClass()
    {
        return $this->repository->getDocumentName();
    }

    /**
     * {@inheritDoc}
     *
     * Currently this is being used to build the route id used for redirecting
     * to newly made documents.
     *
     * We might use a convention based mapping here:
     * Graviton\CoreBundle\Document\App -> mongodb://graviton_core
     * Graviton\CoreBundle\Entity\Table -> mysql://graviton_core
     *
     * @todo implement this in a more convention based manner
     */
    public function getConnectionName()
    {
        return 'graviton_corebundle';
    }
    /**
     * {@inheritDoc}
     *
     * this seems uneeded as soon as we pass in a repository that allows us to get there
     * i think relying on $repositoy->getDocumentManager() is more than ok (ie. the repo
     * has a clear interface.
     *
     * @todo figure out why we would need something like this
     */
    public function setDoctrine()
    {
    }
}
