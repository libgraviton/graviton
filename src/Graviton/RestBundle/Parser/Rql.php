<?php
namespace Graviton\RestBundle\Parser;

use Graviton\RestBundle\Parser;

class Rql implements ParserInterface
{
	private $request;
	
	public function parse()
	{
		
	}
	
	public function setRequest($request)
	{
		$this->request = $request;
	}
}