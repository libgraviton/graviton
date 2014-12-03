<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Graviton\RestBundle\Event\RestEvent;
use Graviton\RestBundle\Action\ActionFactory;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
        $router = $this->container->get('router');
        $this->linkHeader = LinkHeader::fromResponse($response);

        $action = ActionFactory::factory($request, $response);

        if ($action->hasFirstPage()) {
            $url = $action->getFirstPageUrl($router, true);
            $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => "first")));
        }

        if ($action->hasPrevPage()) {
            $url = $action->getPrevPageUrl($router, true);
            $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => "prev")));
        }

        if ($action->hasNextPage()) {
            $url = $action->getNextPageUrl($router, true);
            $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => "next")));
        }

        if ($action->hasLastPage()) {
            $url = $action->getLastPageUrl($router, true);
            $this->linkHeader->add(new LinkHeaderItem($url, array('rel' => "last")));
        }

        $response->headers->set(
            'Link',
            (string) $this->linkHeader
        );

        return;
    }
}
