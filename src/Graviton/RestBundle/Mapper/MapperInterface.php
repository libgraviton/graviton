<?php
namespace Graviton\RestBundle\Mapper;

interface MapperInterface 
{
	public function add($id, $value);
	
	public function remove($id);
	
	public function get($id);
}