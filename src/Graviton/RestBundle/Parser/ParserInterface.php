<?php
namespace Graviton\RestBundle\Parser;

interface ParserInterface
{
	/**
	 * Parse the request
	 * 
	 * @return void;
	 */
	public function parse();
	
	/**
	 * Set the request object
	 * 
	 * @param Object $request Request object
	 * 
	 * @return RestParserInterface $this This
	 */
	public function setRequest($request);
}