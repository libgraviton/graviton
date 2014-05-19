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
        throw new \BadMethodCallException;
    }
    public function deleteRecord($id)
    {
        throw new \BadMethodCallException;
    }
    public function getEntityClass()
    {
        return $this->repository->getDocumentName();
    }
    public function getConnectionName()
    {
        // @todo figure out why we would need something like this
        // currently it is being used to build the route id used for redirecting to newly made documents
        return 'graviton_corebundle';
    }
    public function setDoctrine()
    {
        // @todo figure out why we would need something like this
    }
}
