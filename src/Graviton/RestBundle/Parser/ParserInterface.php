<?php
namespace Graviton\RestBundle\Parser;

interface ParserInterface
{
    /**
     * Parse the request
     * 
     * @return void;
     */
    public function parse($query);
    
    /**
     * Get the parser result
     * 
     * @return object $parserResult $result
     */
    public function getResult();
}
