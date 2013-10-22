<?php
namespace Graviton\RestBundle\Mapper;

class EntityService 
{
	private $arrMap = array();
	private $router;
	
	public function __construct($router)
	{
		$this->router = $router;
	}
	
	public function add($id, $value)
	{
		$this->arrMap[$id] = $value;
		
		return $this;
	}
	
	public function remove($id)
	{
		unset($this->arrMap[$id]);
		
		return $this;
	}
	
	public function get($id, $action, $urlParams)
	{
		$service = $this->arrMap[$id].'_'.strtolower($action);
		$url = $this->router->generate($service, $urlParams);
		
		return $this->$url;
	}
}