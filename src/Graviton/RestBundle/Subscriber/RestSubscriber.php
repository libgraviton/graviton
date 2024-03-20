<?php
/**
 * Class RestSubscriber
 */

namespace Graviton\RestBundle\Subscriber;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\LinkHeaderParser\LinkHeaderItem;
use Graviton\RqlParser\Node\LimitNode;
use Graviton\RqlParser\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class RestSubscriber implements EventSubscriberInterface
{

    /**
     * constructor
     *
     * @param RouterInterface $router router
     */
    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * get events
     *
     * @return array[] events
     */
    #[\Override] public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::REQUEST => [
                ['onRequest', 0]
            ],
            KernelEvents::RESPONSE => [
                ['onResponse', -2]
            ]
        ];
    }

    /**
     * onRequest
     *
     * @param RequestEvent $event event
     * @return void
     */
    public function onRequest(RequestEvent $event): void
    {
        // fill content-type if not set; to make it better for older clients
        $contentType = $event->getRequest()->headers->get('content-type');
        if (empty($contentType)) {
            $event->getRequest()->headers->set('content-type', 'application/json');
        }
    }

    /**
     * onResponse
     * @param ResponseEvent $event event
     * @return void
     */
    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // ensure json charset
        $contentType = $response->headers->get('content-type');
        if ($contentType == 'application/json') {
            $response->headers->set(
                'content-type',
                'application/json; charset=UTF-8'
            );
        }

        // record count header
        if ($request->attributes->has('recordCount')) {
            $response->headers->set(
                'X-Record-Count',
                (string) $request->attributes->get('recordCount')
            );
        }

        // total count header
        if ($request->attributes->has('totalCount')) {
            $response->headers->set(
                'X-Total-Count',
                (string) $request->attributes->get('totalCount')
            );
        }

        // search source header?
        if ($request->attributes->has('X-Search-Source')) {
            $response->headers->set(
                'X-Search-Source',
                (string) $request->attributes->get('X-Search-Source')
            );
        }

        $linkHeader = $this->generateLinkHeader($request, $response);
        if (!empty($linkHeader)) {
            $response->headers->set('Link', $linkHeader);
        }
    }

    /**
     * generates the Link header content
     *
     * @param Request  $request  request
     * @param Response $response response
     * @return string|null header content or null
     */
    private function generateLinkHeader(Request $request, Response $response) : ?string
    {
        $collectionName = $request->attributes->get('collection');
        if (empty($collectionName)) {
            return null;
        }

        $selfUrl = null;
        if ($request->getMethod() == 'POST' && !empty($request->attributes->get('id'))) {
            $putRouteName = sprintf("%s.put", $collectionName);
            try {
                $selfUrl = $this->router->generate(
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
            } catch (\Throwable $t) {
                // ignored
            }
        } else {
            $selfUrl = $this->router->generate(
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params'),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $linkHeader = LinkHeader::fromString($response->headers->get('link'));

        // event status?
        if ($request->attributes->has('eventStatus')) {
            $linkHeader->add(
                new LinkHeaderItem(
                    $request->attributes->get('eventStatus'),
                    ['rel' => 'eventStatus']
                )
            );
        }

        if (!empty($selfUrl)) {
            $queryString = $this->getQueryString($request);
            $linkHeader->add(
                new LinkHeaderItem(
                    empty($queryString) ? $selfUrl : $selfUrl.'?'.$queryString,
                    ['rel' => 'self']
                )
            );
        } else {
            return null;
        }

        // schema link
        try {
            $schemaRouteName = sprintf("%s.schemaJsonGet", $collectionName);
            $schemaUrl = $this->router->generate(
                $schemaRouteName,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkHeader->add(
                new LinkHeaderItem(
                    $schemaUrl,
                    ['rel' => 'schema']
                )
            );
        } catch (\Throwable $t) {
            // ignored
        }

        // paging stuff!
        $perPage = $request->attributes->get('perPage');

        if (empty($perPage)) {
            return (string) $linkHeader;
        }

        $page = $request->attributes->get('page');
        $numPages = $request->attributes->get('numPages');
        $hasNextPage = $request->attributes->get('hasNextPage', false);

        if ($page > 2) {
            $linkHeader->add(
                new LinkHeaderItem(
                    $selfUrl.'?'.$this->getQueryString($request, $perPage, 1),
                    ['rel' => 'first']
                )
            );
        }
        if ($page > 1) {
            $linkHeader->add(
                new LinkHeaderItem(
                    $selfUrl.'?'.$this->getQueryString($request, $perPage, $page - 1),
                    ['rel' => 'prev']
                )
            );
        }
        if ($hasNextPage === true || ($numPages != null && $page < $numPages)) {
            $linkHeader->add(
                new LinkHeaderItem(
                    $selfUrl.'?'.$this->getQueryString($request, $perPage, $page + 1),
                    ['rel' => 'next']
                )
            );
        }
        if ($numPages != null && $page != $numPages) {
            $linkHeader->add(
                new LinkHeaderItem(
                    $selfUrl.'?'.$this->getQueryString($request, $perPage, $numPages),
                    ['rel' => 'last']
                )
            );
        }

        return (string) $linkHeader;
    }

    /**
     * returns the rql query string based on the current request
     *
     * @param Request $request req
     * @param int     $perPage how many per page
     * @param int     $page    which page
     * @return string the query string
     */
    private function getQueryString(Request $request, int $perPage = 0, int $page = 0) : string
    {
        /**
         * @var $query Query
         */
        $query = $request->attributes->get('rqlQuery', new Query());

        if ($perPage < 1) {
            return $query->toRql();
        }

        $limit = $perPage;
        $offset = ($page - 1) * $perPage;

        // apply custom limit
        $query->setLimit(new LimitNode($limit, $offset));

        return $query->toRql();
    }
}
