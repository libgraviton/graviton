<?php
namespace Graviton\RestBundle\Pager;

use Graviton\RestBundle\Pager\PagerInterface;

class Standard implements PagerInterface
{
	private $page;
	
	private $pageSize;
	
	private $totalCount;
	
	public function __construct($page = 1, $pageSize = 20)
	{
		$this->page = $page;
		$this->pageSize = $pageSize;
	}
	
	public function getOffset()
	{
		$offset = $this->pageSize * ($this->page -1);
	
		return $offset;
	}
	
	public function getNextPage()
	{
		$ret = false;

		if (($this->getOffset() + $this->pageSize) < $this->totalCount) {
			$ret = $this->getPage() + 1;
		}
			
		return $ret;
	}
	
	public function getPrevPage()
	{
		$ret = false;
		
		if ($this->page > 1) {
			$ret = $this->page -1;
		}
		
		return $ret;
	}
	
	public function getLastPage()
	{
		$lastPage = floor($this->totalCount / $this->pageSize);
		
		if ($this->totalCount % $this->pageSize) {
			$lastPage += 1;
		}
		
		return $lastPage;
	}
	
	public function getPage()
	{
		return $this->page;
	}
	
	public function getPageSize()
	{
		return $this->pageSize;
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
	
	public function setTotalCount($totalCount)
	{
		$this->totalCount = $totalCount;
	}
}