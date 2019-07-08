<?php
/**
 * add our Link header items
 */

namespace Graviton\RestBundle\Listener;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\LinkHeaderParser\LinkHeaderItem;
use Graviton\RqlParser\Node\LimitNode;
use Graviton\RqlParser\Query;
use Graviton\SchemaBundle\SchemaUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LinkHeaderResponseListener
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var LinkHeader
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
     * add our Link header items
     *
     * @param FilterResponseEvent      $event      response listener event
     * @param string                   $eventName  event name
     * @param EventDispatcherInterface $dispatcher dispatcher
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        $this->linkHeader = LinkHeader::fromString($response->headers->get('link', null));

        // add common headers
        $this->addCommonHeaders($request, $response);

        // add self Link header
        $selfUrl = $this->addSelfLinkHeader($routeName, $routeType, $request);
        // dispatch this!

        // add paging Link element when applicable
        if ($routeType == 'all' && $request->attributes->get('paging')) {
            $this->generatePagingLinksHeaders($routeName, $request, $response);
        }

        // add schema link header element
        $this->generateSchemaLinkHeader($routeName, $request, $response);

        // finally set link header
        $response->headers->set(
            'Link',
            (string) $this->linkHeader
        );

        $event->setResponse($response);

        // dispatch the "selfaware" event
        $event->getRequest()->attributes->set('selfLink', $selfUrl);
        $dispatcher->dispatch($event, 'graviton.rest.response.selfaware');
    }

    /**
     * add common headers
     *
     * @param Request  $request  request
     * @param Response $response response
     *
     * @return void
     */
    private function addCommonHeaders(Request $request, Response $response)
    {
        // replace content-type if a schema was requested
        if ($request->attributes->get('schemaRequest')) {
            $response->headers->set('Content-Type', 'application/schema+json');
        }

        if ($request->attributes->has('recordCount')) {
            $response->headers->set(
                'X-Record-Count',
                (string) $request->attributes->get('recordCount')
            );
        }

        // search source header?
        if ($request->attributes->has('X-Search-Source')) {
            $response->headers->set(
                'X-Search-Source',
                (string) $request->attributes->get('X-Search-Source')
            );
        }
    }

    /**
     * Add "self" Link header item
     *
     * @param string  $routeName route name
     * @param string  $routeType route type
     * @param Request $request   request
     *
     * @return string the "self" link url
     */
    private function addSelfLinkHeader($routeName, $routeType, Request $request)
    {
        $routeParams = $request->get('_route_params');
        if (!is_array($routeParams)) {
            $routeParams = [];
        }

        if (($routeType == 'post' || $routeType == 'postNoSlash') || $routeType != 'all') {
            // handle post request by rewriting self link to newly created resource
            $routeParams['id'] = $request->get('id');
        }

        // rewrite post routes to get
        if ($routeType == 'post' || $routeType == 'postNoSlash' && !empty($routeParams['id'])) {
            $parts = explode('.', $routeName);
            array_pop($parts);
            $parts[] = 'get';
            $routeName = implode('.', $parts);
        }

        if (is_null($routeName)) {
            return;
        }

        $selfLinkUrl = $this->router->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL);
        $queryString = $this->getQueryString($request);

        if (!empty($queryString)) {
            $selfLinkUrl .= '?' . $queryString;
        }

        $this->linkHeader->add(
            new LinkHeaderItem(
                $selfLinkUrl,
                ['rel' => 'self']
            )
        );

        return $selfLinkUrl;
    }


    /**
     * generates the schema rel in the Link header
     *
     * @param string   $routeName route name
     * @param Request  $request   request
     * @param Response $response  response
     *
     * @return void
     */
    private function generateSchemaLinkHeader($routeName, Request $request, Response $response)
    {
        if ($request->get('_route') != 'graviton.core.static.main.all') {
            try {
                $schemaRoute = SchemaUtils::getSchemaRouteName($routeName);
                $this->linkHeader->add(
                    new LinkHeaderItem(
                        $this->router->generate($schemaRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                        ['rel' => 'schema']
                    )
                );
            } catch (\Exception $e) {
                // nothing to do..
            }
        }
    }

    /**
     * generates the paging Link header items
     *
     * @param string   $routeName route name
     * @param Request  $request   request
     * @param Response $response  response
     *
     * @return void
     */
    private function generatePagingLinksHeaders($routeName, Request $request, Response $response)
    {
        $this->generateLinks(
            $routeName,
            $request->attributes->get('page'),
            $request->attributes->get('numPages'),
            $request->attributes->get('perPage'),
            $request
        );

        $response->headers->set(
            'X-Total-Count',
            (string) $request->attributes->get('totalCount')
        );
    }

    /**
     * generate headers for all paging links
     *
     * @param string  $route    name of route
     * @param integer $page     page to link to
     * @param integer $numPages number of all pages
     * @param integer $perPage  number of records per page
     * @param Request $request  request to get rawRql from
     *
     * @return void
     */
    private function generateLinks($route, $page, $numPages, $perPage, Request $request)
    {
        if ($page > 2) {
            $this->generateLink($route, 1, $perPage, 'first', $request);
        }
        if ($page > 1) {
            $this->generateLink($route, $page - 1, $perPage, 'prev', $request);
        }
        if ($page < $numPages) {
            $this->generateLink($route, $page + 1, $perPage, 'next', $request);
        }
        if ($page != $numPages) {
            $this->generateLink($route, $numPages, $perPage, 'last', $request);
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
     *
     * @return string
     */
    private function generateLink(string $routeName, int $page, int $perPage, string $type, Request $request)
    {
        $url = $this->router->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $limit = $perPage;
        $offset = ($page - 1) * $perPage;
        $queryString = $this->getQueryString($request, $limit, $offset);

        if (!empty($queryString)) {
            $url .= '?'.$queryString;
        }

        $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => $type)));
    }

    /**
     * returns the rql query string based on the current request
     *
     * @param Request $request request
     * @param int     $limit   limit
     * @param int     $offset  offset
     *
     * @return string rql query string
     */
    private function getQueryString(Request $request, $limit = 0, $offset = 0)
    {
        /**
         * @var $query Query
         */
        $query = $request->attributes->get('rqlQuery', new Query());

        if ($limit < 1 && $limit < 1) {
            return $query->toRql();
        }

        // apply custom limit
        $query->setLimit(new LimitNode($limit, $offset));

        return $query->toRql();
    }
}
