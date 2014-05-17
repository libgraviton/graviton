<?php

namespace Graviton\RestBundle\Model\Doctrine;

use Graviton\RestBundle\Model\ModelInterface;

class ODM implements ModelInterface
{
    public function find($id)
    {
        return $this->repository->findOneBy(array('id'=>$id));
    }
    public function findAll()
    {
        return $this->repository->findAll();
    }
    public function insertRecord($entity)
    {
        throw \BadMethodCallException;
    }
    public function updateRecord($id, $entity)
    {
        throw \BadMethodCallException;
    }
    public function deleteRecord($id)
    {
        throw \BadMethodCallException;
    }
    public function getEntityClass()
    {
        // @todo why would we do it like this?
    }
    public function getConnectionName()
    {
        // @todo figure out why we would need something like this
    }
    public function setDoctrine()
    {
        // @todo figure out why we would need something like this
    }
}
