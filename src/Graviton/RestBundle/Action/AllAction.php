<?php
namespace Graviton\RestBundle\Action;

/**
 * All Action
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AllAction extends AbstractAction
{
    /**
     * All action
     *
     * This is the most important case. The query string can containt pagination
     * and/or rql params. When generating the url, all these params have to be
     * added as query string. Else pagination and rql wont work together...
     *
     * "page" and "per_page" are easy, they're set as key=value pairs.
     * rql is a bit harder, because the rql params are only separated by &.
     * When using $this->getRequest()->query->all(); the framework will split
     * the rql params into an array with a single param as key and an empty value (Don't know
     * what happens if the rql query contains an "=" sign...)
     * That's why i add the whole query string to the url...
     *
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\AbstractAction::getRefLink()
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        $route = $this->getRoute(self::ACTION_ALL);

        // Generate the base route
        $url = $router->generate($route, array(), $absolute);

        if (!is_null(($queryString = $this->getRequest()->getQueryString()))) {
            $url .= "?".urldecode($queryString);
        }

        return $url;
    }
 
    /**
     * Generate the url to the next page
     * 
     * @param Router  $router   Router
     * @param boolean $absolute Absolute path
     * 
     * @return Ambigous <NULL, string>
     */
    public function getNextPageUrl($router, $absolute = false)
    {
    	$url = null;
    	$params = $this->getPaginationParams();
    	
    	if ($this->hasNextPage()) {
    		$params = $this->getPaginationParams();
    		$params['page']++;
	 
    		$url = $this->generateUrl($router, $params, $absolute);
    	}

    	return $url;
    }
    
    /**
     * Generate the url to the last page
     * 
     * @param Router  $router   Router
     * @param boolean $absolute Absolute path
     * 
     * @return 
     */
    public function getPrevPageUrl($router, $absolute = false)
    {
    	$url = null;
    	
    	if ($this->hasPrevPage()) {
    		$params = $this->getPaginationParams();
    		$params['page']--;
    		
    		$url = $this->generateUrl($router, $params, $absolute);
    	}
    	
    	return $url;
    }
    
    public function getLastPageUrl($router, $absolute = false)
    {
    	$url = null;
    	
    	if ($this->hasLastPage()) {
    		$params = $this->getPaginationParams();
    		$params['page'] = $this->getRequest()->attributes->get('numPages');
    		
    		$url = $this->generateUrl($router, $params, $absolute);
    	}
    
    	return $url;
    }
}
