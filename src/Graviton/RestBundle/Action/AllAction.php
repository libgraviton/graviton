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
