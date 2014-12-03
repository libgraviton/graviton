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
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @see \Graviton\RestBundle\Action\AbstractAction::getRefLink()
     *
     * @return string $url Url
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        $params = $this->getPaginationParams();

        $url = $this->generateUrl($router, self::ACTION_ALL, $params, $absolute);

        return $url;
    }

    /**
     * (non-PHPdoc)
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @see \Graviton\RestBundle\Action\AbstractAction::getNextPageUrl()
     *
     * @return string url Url or empty string
     */
    public function getNextPageUrl($router, $absolute = false)
    {
        $url = "";

        if ($this->hasNextPage()) {
            $params = $this->getPaginationParams();
            $params['page']++;

            $url = $this->generateUrl($router, self::ACTION_ALL, $params, $absolute);
        }

        return $url;
    }

    /**
     * (non-PHPdoc)
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @see \Graviton\RestBundle\Action\AbstractAction::getPrevPageUrl()
     *
     * @return string $url Url or empty string
     */
    public function getPrevPageUrl($router, $absolute = false)
    {
        $url = "";

        if ($this->hasPrevPage()) {
            $params = $this->getPaginationParams();
            $params['page']--;

            $url = $this->generateUrl($router, self::ACTION_ALL, $params, $absolute);
        }

        return $url;
    }

    /**
     * (non-PHPdoc)
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @see \Graviton\RestBundle\Action\AbstractAction::getLastPageUrl()
     *
     * @return string $url Url or empty string
     */
    public function getFirstPageUrl($router, $absolute = false)
    {
        $url = "";

        if ($this->hasFirstPage()) {
            $params = $this->getPaginationParams();
            $params['page'] = 1;

            $url = $this->generateUrl($router, self::ACTION_ALL, $params, $absolute);
        }

        return $url;
    }
    
    /**
     * (non-PHPdoc)
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @see \Graviton\RestBundle\Action\AbstractAction::getLastPageUrl()
     *
     * @return string $url Url or empty string
     */
    public function getLastPageUrl($router, $absolute = false)
    {
    	$url = "";
    
    	if ($this->hasLastPage()) {
    		$params = $this->getPaginationParams();
    		$params['page'] = $this->getRequest()->attributes->get('numPages');
    
    		$url = $this->generateUrl($router, self::ACTION_ALL, $params, $absolute);
    	}
    
    	return $url;
    }
}
