<?php

namespace Graviton\RestBundle\Listener\Response;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class DocumentLinkHeadersEvent implements ContainerAwareInterface
{
    private $container;
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $router = $this->container->get('router');

        // extract various info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = array_slice($routeParts, -1, 1, true);

        // for now we assume that everything except collections has an id
        // this is also flawed since it does not handle search actions
        $parameters = array();
        if ($routeType != 'all') {
            $parameters = array('id' => $request->get('id'));
        }

        $url = $router->generate($routeName, $parameters, true);

        // append rel=self link to link headers
        $links = explode(', ', $response->headers->get('Link'));
        $links = array_filter($links);
        $links[] = sprintf('<%s>; rel="self"', $url);

        // overwrite link headers with new headers
        $response->headers->set('Link', implode(', ', $links));

        $event->setResponse($response);
    }
}
