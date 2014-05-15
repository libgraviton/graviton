<?php
namespace Graviton\RestBundle\Parser;

use Graviton\RestBundle\Parser\ParserInterface;

class Rql implements ParserInterface
{
	private $query = array();
	private $parserResult;
	
	public function parse($query)
	{
		$this->query = $query;
		
		return $this;
	}
	
	public function getResult()
	{
		return $this->parserResult;
	}
}