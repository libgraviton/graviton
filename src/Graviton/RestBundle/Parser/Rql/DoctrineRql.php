<?php
namespace Graviton\RestBundle\Parser\Rql;

use Graviton\RestBundle\Parser\ParserInterface;

class DoctrineRql implements ParserInterface
{
	private $request;
	
	private $ignoreParams = array(
	    'page',
		'pageSize'		
	);
	
	public function parse()
	{
		$requestQuery = $this->request->query->all();
		
		
	}
	
	public function append($query)
	{
		
	}
	
	public function setRequest($request)
	{
		$this->request = $request;
	}
}