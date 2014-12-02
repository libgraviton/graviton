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
abstract class AbstractAction implements ActionInterface
{
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
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::hasNextPage()
     *
     * @return bool $ret true/false
     */
    public function hasNextPage()
    {
        $ret = false;

        if (null !== $this->getRequest()->attributes->get('paging')) {
            $lastPage = $this->getRequest()->attributes->get('numPages');
            $page = $this->getRequest()->query->get('page');

            if ($lastPage > $page) {
                $ret = true;
            }
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::hasPrevPage()
     *
     * @return bool $ret true/false
     */
    public function hasPrevPage()
    {
        $ret = false;

        if (null !== $this->getRequest()->attributes->get('paging')) {
            if ($this->getRequest()->query->get('page') > 1) {
                $ret = true;
            }
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::hasLastPage()
     *
     * @return bool $ret true/false
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
     * Get the rel=self url
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @return string $url Url (rel=self)
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        $id = $this->getRequest()->get('id');
        $url = $this->generateUrl($router, self::ACTION_GET, array('id' => $id), $absolute);

        return $url;
    }

    /**
     * Get the rel=next url
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absoulte Absolute path
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::getNextLink()
     *
     * @return string $ret Url or empty string
     */
    public function getNextPageUrl($router, $absoulte = false)
    {
        return "";
    }

    /**
     * Get the rel=prev url
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absoulte Absolute path
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::getPrevLink()
     *
     * @return string $ret Url or empty string
     */
    public function getPrevPageUrl($router, $absoulte = false)
    {
        return "";
    }

    /**
     * Get the rel=last url
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absoulte Absolute path
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::getLastLink()
     *
     * @return string $ret Url or empty string
     */
    public function getLastPageUrl($router, $absoulte = false)
    {
        return "";
    }

    /**
     * Returns an array with pagination params if set
     *
     * @return multitype:number
     */
    protected function getPaginationParams()
    {
        $params = array();

        if (null !== $this->getRequest()->attributes->get('paging')) {
            $params['page'] = (int) $this->getRequest()->get('page', 1);
            $params['per_page'] = (int) $this->getRequest()->attributes->get('perPage');
        }

        return $params;
    }

    /**
     * Generate an url with the given parameters
     *
     * I'm not sure if this realy works. Maybe one needs to refactor this.
     * The RQL Parser is able to parse the url and exract the necessary parameters.
     * Something like that could do the trick...
     *
     * @param Router $router   Router
     * @param string $action   Action (defined in ActionInterface)
     * @param array  $params   Parameters
     * @param string $absolute Absolute path
     *
     * @return string $url Url
     */
    protected function generateUrl(
        $router,
        $action,
        $params = array(),
        $absolute = false
    ) {
        $delimiter = '?';

        if (!empty($params)) {
            $delimiter = '&';
        }

        $url = $router->generate(
            $this->getRoute($action),
            $params,
            $absolute
        );

        // get the query string and remove page/per_page params
        $queryString = $this->removePaginationParams(
            $this->getRequest()->getQueryString()
        );

        if (!empty($queryString)) {
            $url .= $delimiter.urldecode($queryString);
        }

        return $url;
    }

    /**
     * Get the route to this action
     *
     * @param string $actionName Name of this action (defined in ActionInterface)
     *
     * @see \Graviton\RestBundle\Action\ActionInterface::getRoute()
     *
     * @return string $route Route identifier of this action
     */
    protected function getRoute($actionName)
    {
        $routeParts = explode('.', $this->request->get('_route'));

        // Replace the last part of the route (post, put...) with action name
        array_pop($routeParts);
        array_push($routeParts, $actionName);

        return implode(".", $routeParts);
    }

    /**
     * Remove the pagination params from query string (if set)
     *
     * @param string $queryString Query string from request object
     *
     * @return string $queryString Normalized query string without pagination params
     */
    protected function removePaginationParams($queryString = "")
    {
        $params = $this->getPaginationParams();

        if (!empty($params['page'])) {
            $search = "page=".$params['page'];
            $queryString = str_replace($search, "", $queryString);
        }

        if (!empty($params['per_page'])) {
            $search = "&per_page=".$params['per_page'];
            $queryString = str_replace($search, "", $queryString);
        }

        // It's possible that there are some "&" sign left in the query string
        // Use Request::normalize to remove them
        $queryString = Request::normalizeQueryString($queryString);

        return $queryString;
    }
}
