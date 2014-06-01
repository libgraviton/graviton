<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

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
        if ($routeType == 'all' && $request->attributes->get('paging')) {

            $header = $response->headers->get('Link');
            if (is_array($header)) {
                implode(',', $header);
            }
            $linkHeader = LinkHeader::fromString($header);

            $page = $request->get('page', 1);
            $numPages = $request->attributes->get('numPages');

            if ($page > 2) {
                $this->generateLink($linkHeader, $router, $routeName, 1, 'first');
            }
            if ($page > 1) {
                $this->generateLink($linkHeader, $router, $routeName, $page - 1, 'prev');
            }
            if ($page < $numPages) {
                $this->generateLink($linkHeader, $router, $routeName, $page + 1, 'next');
            }
            if ($page != $numPages) {
                $this->generateLink($linkHeader, $router, $routeName, $numPages, 'last');
            }

            // overwrite link headers with new headers
            $response->headers->set('Link', (string) $linkHeader);
        }

        $event->setResponse($response);
    }

    /**
     * generate link header pased on params and type
     *
     * @param LinkHeader &$linkHeader link header api
     * @param Router     $router      router used to generate urls
     * @param string     $routeName   use with router to generate urls
     * @param integer    $page        page to link to
     * @param string     $type        rel type of link to generate
     *
     * @return string
     */
    private function generateLink(&$linkHeader, $router, $routeName, $page, $type)
    {
        $url = $router->generate($routeName, array('page' => $page), true);
        $linkHeader->add(new LinkHeaderItem($url, array('rel' => $type)));
    }
}
