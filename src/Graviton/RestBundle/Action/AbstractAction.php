<?php
namespace Graviton\RestBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract action
 *
 * There is a class for every possible action (all,get,put...)
 * Feel free to add more functionality to this interface and these classes (or
 * refactory it if it doesn't fit your needs...).
 * For now, we use it to generate the right ref to this action.
 *
 * At the moment, there is no acl implemented, but an "isAllowed" method could
 * be a candidate for this class (together with a request listener that throw an exception if not)
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AbstractAction implements ActionInterface
{
	const ACTION = self::ACTION_ALL;
	
    /**
     * Request
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Request
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * Constructor
     *
     * @param Request  $request  Request object
     * @param Response $response Response object
     *
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::getRoute()
     */
    public function getRoute($actionName)
    {
        $routeParts = explode('.', $this->request->get('_route'));

        // Replace the last part of the route (post, put...) with action name
        array_pop($routeParts);
        array_push($routeParts, $actionName);

        return implode(".", $routeParts);
    }

    /**
     * Return the request objecg
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::getRefLink()
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        // This is the default case (get, put, post)
        $route = $this->getRoute(self::ACTION_GET);
        $id = $this->getRequest()->get('id');

        return $router->generate($route, array('id' => $id), $absolute);
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::hasNextPage()
     */
    public function hasNextPage()
    {
    	$ret = false;
    	 
    	if (null !== $this->getRequest()->attributes->get('paging')) {
    		$lastPage = $this->getRequest()->attributes->get('numPages');
    		$page = $this->getRequest()->attributes->get('page');
    
    		if ($lastPage > $page) {
    			$ret = true;
    		}
    	}
    
    	return $ret;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::hasPrevPage()
     */
    public function hasPrevPage()
    {
    	$ret = false;
    	 
    	if (null !== $this->getRequest()->attributes->get('page')) {
    		if ($this->getRequest()->attributes->get('page') > 1) {
    			$ret = true;
    		}
    	}
    	 
    	return $ret;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::hasLastPage()
     */
    public function hasLastPage()
    {
    	$ret = false;
    	 
    	if (null !== $this->getRequest()->attributes->get('numPages')) {
    		$ret = true;
    	}
    	 
    	return $ret;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::getNextLink()
     */
    public function getNextPageUrl($router, $absoulte = false)
    {
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::getPrevLink()
     */
    public function getPrevPageUrl($router, $absoulte = false)
    {
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\ActionInterface::getLastLink()
     */
    public function getLastPageUrl($router, $absoulte = false)
    {
        return null;
    }
    
    protected function getPaginationParams()
    {
    	$params = array();
    	 
    	if (null !== $this->getRequest()->attributes->get('paging')) {
    		$params['page'] = (int) $this->getRequest()->get('page', 1);
    		$params['per_page'] = (int) $this->getRequest()->attributes->get('perPage');
    	}
    	 
    	return $params;
    }
    
    protected function removePaginationParams($queryString = "")
    {
    	$params = $this->getPaginationParams();
    	 
    	$search = "page=".$params['page'];
    	$queryString = str_replace($search, "", $queryString);
    	
    	$search = "&per_page=".$params['per_page'];
    	$queryString = str_replace($search, "", $queryString);
    	 
    	return $queryString;
    }
    
    /**
     * Generate an url with the given parameters
     * 
     * I'm not sure if this realy work. Maybe one needs to refactor this.
     * The RQL Parser is able to parse the url and exract the necessary parameters.
     * Something like that could do the trick...
     * 
     * @param Router $router   Router
     * @param array  $params   Parameters
     * @param string $absolute Absolute path
     * 
     * @return string $url Url 
     */
    protected function generateUrl($router, $params = array(), $absolute = false)
    {
    	$delimiter = '?';
    	
    	if (!empty($params)) {
    		$delimiter = '&';
    	}
    	
    	$url = $router->generate(
    		$this->getRoute(static::ACTION),
    		$params,
    		$absolute
    	);
    	
    	// get the query string and remove page/per_page params
    	$queryString = $this->removePaginationParams(
    		$this->getRequest()->getQueryString()
    	);

    	// It's possible that there are some "&" sign left in the query string
    	// Use Request::normalize to remove them
    	$queryString = Request::normalizeQueryString($queryString);
    	
    	if (!empty($queryString)) {
    		$url .= $delimiter.urldecode($queryString);
    	}

    	return $url;
    }
}
