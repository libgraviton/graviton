<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Graviton\RestBundle\Event\RestEvent;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SelfLinkResponseListener implements ContainerAwareInterface
{
    /**
     * @private reference to service_container
     */
    private $container;

    /**
     * {@inheritDoc}
     *
     * @param ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * add a rel=self Link header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(RestEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $router = $this->container->get('router');
        $linkHeader = LinkHeader::fromResponse($response);

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4).'get';
        }

        /** if the request failed in the RestController, $request will not have an record id in
         case of a POST and $router->generate() will fail. that's why we catch it and fail silently
         by not including our header in the response. i hope that's a good compromise. **/

        $addHeader = true;
        $url = '';

        try {
            $url = $router->generate($routeName, $this->generateParameters($routeType, $request), true);
        } catch (\Exception $e) {
            $addHeader = false;
        }

        if ($addHeader) {
            // append rel=self link to link headers
            $linkHeader->add(new LinkHeaderItem($url, array('rel' => 'self')));

            // overwrite link headers with new headers
            $response->headers->set('Link', (string) $linkHeader);

            // $event->setResponse($response);
        }
    }

    /**
     * generate parameters for LinkHeaderItem
     *
     * @param string  $routeType type of route
     * @param Request $request   request object
     *
     * @return array
     */
    private function generateParameters($routeType, Request $request)
    {
        // for now we assume that everything except collections has an id
        // this is also flawed since it does not handle search actions
        $parameters = array();
        if ($routeType == 'post') {
            // handle post request by rewriting self link to newly created resource
            $parameters = array('id' => $request->get('id'));
        } elseif ($routeType != 'all') {
            $parameters = array('id' => $request->get('id'));
        } elseif ($request->attributes->get('paging')) {
            $parameters = array('page' => $request->get('page', 1));
            if ($request->attributes->get('perPage')) {
                $parameters['per_page'] = $request->attributes->get('perPage');
            }
        }

        return $parameters;
    }
}
