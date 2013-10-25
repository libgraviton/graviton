<?php
namespace Graviton\RestBundle\Pager;

interface PagerInterface
{
	/**
	 * Get the page
	 * 
	 * @return Number $page Page
	 */
	public function getPage();
	
	/**
	 * Get the page size
	 * 
	 * @return Number $pageSize Page size
	 */
	public function getPageSize();
	
	/**
	 * Get calculated offset
	 * 
	 * @return Number $offset Calculated offset
	 */
	public function getOffset();
	
	/**
	 * Set the page
	 * 
	 * @param Number $page Page
	 * 
	 * @return RestPagerInterface $this This
	 */
	public function setPage($page);
	
	/**
	 * Set the page size
	 * 
	 * @param Number $pageSize The page size
	 * 
	 * @return RestPagerInterface $this This
	 */
	public function setPageSize($pageSize);
	
	public function setRequest($request);
}