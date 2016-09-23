<?php
/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PagingLinkResponseListener
{
    use GetRqlUrlTrait;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var \Graviton\RestBundle\HttpFoundation\LinkHeader
     */
    private $linkHeader;

    /**
     * @param Router $router router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * add a rel=self Link header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        // only collections have paging
        if ($routeType == 'all' && $request->attributes->get('paging')) {
            $rql = '';
            if ($request->attributes->get('hasRql', false)) {
                $rql = $request->attributes->get('rawRql', '');
            }

            $this->linkHeader = LinkHeader::fromResponse($response);

            $this->generateLinks(
                $routeName,
                $request->attributes->get('page'),
                $request->attributes->get('numPages'),
                $request->attributes->get('perPage'),
                $request,
                $rql
            );
            $response->headers->set(
                'Link',
                (string) $this->linkHeader
            );
            $response->headers->set(
                'X-Total-Count',
                (string) $request->attributes->get('totalCount')
            );
        }
    }

    /**
     * generate headers for all paging links
     *
     * @param string  $route    name of route
     * @param integer $page     page to link to
     * @param integer $numPages number of all pages
     * @param integer $perPage  number of records per page
     * @param Request $request  request to get rawRql from
     * @param string  $rql      rql query string
     *
     * @return void
     */
    private function generateLinks($route, $page, $numPages, $perPage, Request $request, $rql)
    {
        if ($page > 2) {
            $this->generateLink($route, 1, $perPage, 'first', $request, $rql);
        }
        if ($page > 1) {
            $this->generateLink($route, $page - 1, $perPage, 'prev', $request, $rql);
        }
        if ($page < $numPages) {
            $this->generateLink($route, $page + 1, $perPage, 'next', $request, $rql);
        }
        if ($page != $numPages) {
            $this->generateLink($route, $numPages, $perPage, 'last', $request, $rql);
        }
    }

    /**
     * generate link header passed on params and type
     *
     * @param string  $routeName use with router to generate urls
     * @param integer $page      page to link to
     * @param integer $perPage   number of items per page
     * @param string  $type      rel type of link to generate
     * @param Request $request   request to get rawRql from
     * @param string  $rql       rql query string
     *
     * @return string
     */
    private function generateLink($routeName, $page, $perPage, $type, Request $request, $rql)
    {
        $limit = '';
        if ($perPage) {
            $page = ($page - 1) * $perPage;
            $limit = sprintf('limit(%s,%s)', $perPage, $page);
        }
        if (strpos($rql, 'limit') !== false) {
            $rql = preg_replace('/limit\(.*\)/U', $limit, $rql);
        } else {
            $rql .= '&'.$limit;
        }

        $url = $this->getRqlUrl(
            $request,
            $this->router->generate($routeName, [], true) . '?' . strtr($rql, [',' => '%2C'])
        );

        $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => $type)));
    }
}
