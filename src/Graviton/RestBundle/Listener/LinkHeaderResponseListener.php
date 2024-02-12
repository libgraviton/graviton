<?php
/**
 * add our Link header items
 */

namespace Graviton\RestBundle\Listener;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\LinkHeaderParser\LinkHeaderItem;
use Graviton\RqlParser\Node\LimitNode;
use Graviton\RqlParser\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;

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
     * @param ResponseEvent            $event      response listener event
     * @param string                   $eventName  event name
     * @param EventDispatcherInterface $dispatcher dispatcher
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        // extract various info from route
        $routeName = $request->get('_route');

        $this->linkHeader = LinkHeader::fromString($response->headers->get('link'));

        // add self Link header
        $selfUrl = $this->addSelfLinkHeader($request, $response);

        if (!empty($selfUrl)) {
            $this->linkHeader->add(
                new LinkHeaderItem(
                    $selfUrl,
                    ['rel' => 'self']
                )
            );

            // dispatch the "selfaware" event
            $event->getRequest()->attributes->set('selfLink', $selfUrl);
            $dispatcher->dispatch($event, 'graviton.rest.response.selfaware');
        }

        // add paging Link element when applicable
        $this->generatePagingLinksHeaders($request, $response);

        // add schema link header element
        $this->generateSchemaLinkHeader($request, $response);

        // finally set link header
        $response->headers->set(
            'Link',
            (string) $this->linkHeader
        );

        $event->setResponse($response);
    }

    /**
     * Add "self" Link header item
     *
     * @param Request  $request request
     * @param Response  $response response
     *
     * @return string the "self" link url or null
     */
    private function addSelfLinkHeader(Request $request, Response $response) : ?string
    {
        // was an entity created?
        if ($request->getMethod() == 'POST' && !empty($request->attributes->get('id'))) {
            // yes!
            $routeParts = explode('.', $request->get('_route'));
            $putRouteName = $routeParts[0].'.put';

            try {
                $fullUrl = $this->router->generate(
                    $putRouteName,
                    ['id' => $request->attributes->get('id')],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $relativeUrl = $this->router->generate(
                    $putRouteName,
                    ['id' => $request->attributes->get('id')],
                    UrlGeneratorInterface::ABSOLUTE_PATH
                );

                $response->headers->set('location', $relativeUrl);

                return $fullUrl;
            } catch (\Throwable $t) {
                // ignored
            }
        }

        // normal url creation
        $selfLinkUrl = $this->router->generate(
            $request->get('_route'),
            $request->get('_route_params'),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $queryString = $this->getQueryString($request);

        if (!empty($queryString)) {
            $selfLinkUrl .= '?' . $queryString;
        }

        return $selfLinkUrl;
    }

    /**
     * generates the schema rel in the Link header
     *
     * @param Request  $request   request
     * @param Response $response  response
     *
     * @return void
     */
    private function generateSchemaLinkHeader(Request $request, Response $response)
    {
        $routeName = $request->attributes->get('_route');

        // is there a schema route?
        $routeNameParts = explode(".", $routeName);
        $schemaRouteName = $routeNameParts[0].'.schemaJsonGet';

        if ($this->router->getRouteCollection()->get($schemaRouteName) instanceof Route) {
            try {
                $this->linkHeader->add(
                    new LinkHeaderItem(
                        $this->router->generate($schemaRouteName, [], UrlGeneratorInterface::ABSOLUTE_URL),
                        ['rel' => 'schema']
                    )
                );
            } catch (\Exception $e) {
                // nothing to do.
            }
        }
    }

    /**
     * generates the paging Link header items
     *
     * @param Request  $request   request
     * @param Response $response  response
     *
     * @return void
     */
    private function generatePagingLinksHeaders(Request $request, Response $response)
    {
        if ($request->attributes->has('totalCount')) {
            $response->headers->set(
                'X-Total-Count',
                (string) $request->attributes->get('totalCount')
            );
        }

        // perPage is always needed -> don't do anything if not here..
        $perPage = $request->attributes->get('perPage');

        if (empty($perPage)) {
            return;
        }

        $routeName = $request->attributes->get('_route');

        $page = $request->attributes->get('page');
        $numPages = $request->attributes->get('numPages');
        $hasNextPage = $request->attributes->get('hasNextPage', false);

        if ($page > 2) {
            $this->generateLink($routeName, 1, $perPage, 'first', $request);
        }
        if ($page > 1) {
            $this->generateLink($routeName, $page - 1, $perPage, 'prev', $request);
        }
        if ($hasNextPage === true || ($numPages != null && $page < $numPages)) {
            $this->generateLink($routeName, $page + 1, $perPage, 'next', $request);
        }
        if ($numPages != null && $page != $numPages) {
            $this->generateLink($routeName, $numPages, $perPage, 'last', $request);
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

        if ($limit < 1) {
            return $query->toRql();
        }

        // apply custom limit
        $query->setLimit(new LimitNode($limit, $offset));

        return $query->toRql();
    }
}
