<?php
namespace Graviton\RestBundle\Pager;

use Graviton\RestBundle\Pager\PagerInterface;

class Standard implements PagerInterface
{
	private $page;
	
	private $pageSize;
	
	private $request;
	
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
		$requestQuery = $this->request->query->all();

		if (isset($requestQuery['page']) && $requestQuery['page'] > 0) {
			$this->page = $requestQuery['page'];
		}
		
		if (isset($requestQuery['pageSize'])) {
			$this->pageSize = $requestQuery['pageSize'];
		}
		
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
	
	public function setRequest($request)
	{
		$this->request = $request;
	}
}