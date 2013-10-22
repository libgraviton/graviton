<?php
namespace Graviton\RestBundle\Parser;

// dummy class
class Rql
{
	public function applyFilter($query)
	{
		$query->setFirstResult($offset);
		$query->setMaxResults(20);
	}
}