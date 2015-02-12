<?php
/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Graviton\RestBundle\Event\RestEvent;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PagingLinkResponseListener implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * @var \Graviton\RestBundle\HttpFoundation\LinkHeader
     */
    private $linkHeader;

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

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        // only collections have paging
        if ($routeType == 'all' && $request->attributes->get('paging')) {
            $additionalParams = array();
            if ($request->attributes->get('filtering')) {
                $additionalParams['q'] = $request->get('q', '');
            }

            $this->linkHeader = LinkHeader::fromResponse($response);

            $this->generateLinks(
                $routeName,
                $request->get('page', 1),
                $request->attributes->get('numPages'),
                $request->attributes->get('perPage'),
                $additionalParams
            );
            $response->headers->set(
                'Link',
                (string) $this->linkHeader
            );
        }
    }

    /**
     * generate headers for all paging links
     *
     * @param string  $route            name of route
     * @param integer $page             current page
     * @param integer $numPages         number of all pages
     * @param integer $perPage          number of records per page
     * @param array   $additionalParams Optional array of additional params to include
     *
     * @return void
     */
    private function generateLinks($route, $page, $numPages, $perPage, $additionalParams = array())
    {
        if ($page > 2) {
            $this->generateLink($route, 1, $perPage, 'first', $additionalParams);
        }
        if ($page > 1) {
            $this->generateLink($route, $page - 1, $perPage, 'prev', $additionalParams);
        }
        if ($page < $numPages) {
            $this->generateLink($route, $page + 1, $perPage, 'next', $additionalParams);
        }
        if ($page != $numPages) {
            $this->generateLink($route, $numPages, $perPage, 'last', $additionalParams);
        }
    }

    /**
     * generate link header passed on params and type
     *
     * @param string  $routeName        use with router to generate urls
     * @param integer $page             page to link to
     * @param integer $perPage          number of items per page
     * @param string  $type             rel type of link to generate
     * @param array   $additionalParams Optional array of additional params to include
     *
     * @return string
     */
    private function generateLink($routeName, $page, $perPage, $type, $additionalParams = array())
    {
        $router = $this->container->get('router');
        $parameters = array_merge($additionalParams, array('page' => $page));
        if ($perPage) {
            $parameters['perPage'] = $perPage;
        }
        $url = $router->generate($routeName, $parameters, true);
        $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => $type)));
    }
}
