<?php

namespace Graviton\SchemaBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Add a Link header to a schema endpoint to a response
 */
class SchemaLinkResponseListener implements ContainerAwareInterface
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

        // extract info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        list($app, $module, $routeType, $model, $method) = $routeParts;

        $schemaRouteName = 'graviton.schema.get';
        $parameters = array('routePath' => implode('/', array($module, $model)));

        $schema = 'application/vnd.graviton.schema.core.app+json';

        if ($method == 'all') {
            $parameters['routePath'] = 'schema/collection';
            $schema = 'application/vnd.graviton.schema.collection+json';
        }

        $url = $router->generate($schemaRouteName, $parameters, true);

        // append rel=self link to link headers
        $links = explode(', ', $response->headers->get('Link'));
        $links = array_filter($links);
        $links[] = sprintf('<%s>; rel="schema"; type="%s"', $url, $schema);

        // overwrite link headers with new headers
        $response->headers->set('Link', implode(',', $links));

        $event->setResponse($response);
    }
}
