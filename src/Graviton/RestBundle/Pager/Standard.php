<?php
namespace Graviton\RestBundle\Pager;

use Graviton\RestBundle\Pager\PagerInterface;

class Standard implements PagerInterface
{
	private $page;
	
	private $pageSize;
	
	public function __construct($page = 1, $pageSize = 20)
	{
		$this->page = $page;
		$this->pageSize = $pageSize;
	}
	
	public function getPage()
	{
		return $this->page;
	}
	
	public function getPageSize()
	{
		return $this->pageSize;
	}
	
	public function getOffset()
	{
		$offset = $this->pageSize * ($this->page -1);

		return $offset;
	}
	
	public function setPage($page)
	{
		$this->page = $page;
		
		return $this;
	}
	
	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		
		return $this;
	}
}