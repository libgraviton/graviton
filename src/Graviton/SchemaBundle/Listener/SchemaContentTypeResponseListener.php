<?php

namespace Graviton\SchemaBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\SchemaBundle\SchemaUtils;

/**
 * Add a Link header to a schema endpoint to a response
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class SchemaContentTypeResponseListener implements ContainerAwareInterface
{
    /**
     * @private ContainerInterface
     */
    private $container;

    /**
     * inject a service_container
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
     * Add rel=schema Link header for most routes
     *
     * This does not add a link to routes used by the schema bundle
     * itself.
     *
     * @param FilterResponseEvent $event response event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $router = $this->container->get('router');

        // build content-type string
        $contentType = 'application/json; charset=UTF-8';
        if ($request->get('_route') != 'graviton.core.static.main.all') {

            $schemaRoute = SchemaUtils::getSchemaRouteName($request->get('_route'));
            $contentType .= sprintf('; profile=%s', $router->generate($schemaRoute, array(), true));
        }

        // replace content-type if a schema was requested
        if ($request->attributes->get('schemaRequest')) {
            $contentType = 'application/schema+json';
        }
        $response->headers->set('Content-Type', $contentType);

        $event->setResponse($response);
    }
}
