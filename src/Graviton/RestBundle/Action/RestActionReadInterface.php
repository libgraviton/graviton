<?php
namespace Graviton\RestBundle\Action;

interface RestActionReadInterface
{
	public function getOne($id, $request, $entityClass, $connection);
	
	public function getAll($page, $pageSize, $request, $entityClass, $connection);
}