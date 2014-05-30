<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class PagingLinkResponseListener implements ContainerAwareInterface
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
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $router = $this->container->get('router');

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        // only collections have paging
        $parameters = array();
        if ($routeType == 'all' && $request->attributes->get('paging')) {
            $links = explode(', ', $response->headers->get('Link'));
            $links = array_filter($links);

            $page = $request->get('page', 1);
            $numPages = $request->attributes->get('numPages');

            if ($page > 2) {
                $url = $router->generate($routeName, array('page' => 1), true);
                $links[] = sprintf('<%s>; rel="first"', $url);
            }
            if ($page > 1) {
                $url = $router->generate($routeName, array('page' => $page - 1), true);
                $links[] = sprintf('<%s>; rel="prev"', $url);
            }
            if ($page < $numPages) {
                $url = $router->generate($routeName, array('page' => $page + 1), true);
                $links[] = sprintf('<%s>; rel="next"', $url);
            }
            if ($page != $numPages) {
                $url = $router->generate($routeName, array('page' => $numPages), true);
                $links[] = sprintf('<%s>; rel="last"', $url);
            }

            // overwrite link headers with new headers
            $response->headers->set('Link', implode(',', $links));
        }

        $event->setResponse($response);
    }
}
