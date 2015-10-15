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
use Graviton\RestBundle\Event\RestEvent;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SelfLinkResponseListener
{
    use GetRqlUrlTrait;

    /**
     * @var Router
     */
    private $router;

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
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $linkHeader = LinkHeader::fromResponse($response);

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4).'get';
        }

        if ($routeType == 'postNoSlash') {
            $routeName = substr($routeName, 0, -11).'get';
        }

        /** if the request failed in the RestController, $request will not have an record id in
         case of a POST and $router->generate() will fail. that's why we catch it and fail silently
         by not including our header in the response. i hope that's a good compromise. **/

        /** Nope, it's not a good compromise...catch and handle it where it happens.
         *  I will refactory this in another branch*/
        $addHeader = true;
        $url = '';

        try {
            $params = $this->generateParameters($routeType, $request);
            $query = '';
            if (array_key_exists('q', $params)) {
                $query = '?' . strtr($params['q'], [',' => '%2C']);
                unset($params['q']);
            }
            $url = $this->getRqlUrl(
                $request,
                $this->router->generate($routeName, $params, true) . $query
            );

        } catch (\Exception $e) {
            $addHeader = false;
        }

        if ($addHeader) {
            // append rel=self link to link headers
            $linkHeader->add(new LinkHeaderItem($url, array('rel' => 'self')));

            // overwrite link headers with new headers
            $response->headers->set('Link', (string) $linkHeader);

            // set in request and dispatch new event for interested parties
            $event->getRequest()->attributes->set('selfLink', $url);
            $event->getDispatcher()->dispatch('graviton.rest.response.selfaware', $event);
        }
    }

    /**
     * generate parameters for LinkHeaderItem
     *
     * @param string  $routeType type of route
     * @param Request $request   request object
     *
     * @return array
     *
     * @todo we need to refactor this as soon as we add another param, it already mixes paging and filtering too much
     */
    private function generateParameters($routeType, Request $request)
    {
        // for now we assume that everything except collections has an id
        // this is also flawed since it does not handle search actions
        $parameters = array();

        if ($routeType == 'post' || $routeType == 'postNoSlash') {
            // handle post request by rewriting self link to newly created resource
            $parameters = array('id' => $request->get('id'));
        } elseif ($routeType != 'all') {
            $parameters = array('id' => $request->get('id'));
        }

        if ($routeType == 'all' && $request->attributes->get('paging')) {
            // no rql given, we can do our own limit
            $limit = sprintf(
                'limit(%s,%s)',
                $request->attributes->get('startAt'),
                $request->attributes->get('perPage')
            );

            $parameters = ['q' => $limit];
        }

        if ($routeType == 'all' && $request->attributes->get('hasRql')) {
            $parameters = ['q' => $request->attributes->get('rawRql')];
        }

        return $parameters;
    }
}
