<?php
namespace Graviton\RestBundle\Action;

interface RestActionWriteInterface
{
	public function write($id, $request, $entityClass, $connection);
	
	public function delete($id, $modelClass, $connection);
}