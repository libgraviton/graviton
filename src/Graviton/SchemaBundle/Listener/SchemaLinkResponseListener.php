<?php

namespace Graviton\SchemaBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Add a Link header to a schema endpoint to a response
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SchemaLinkResponseListener implements ContainerAwareInterface
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
        $response = $event->getResponse();
        $request = $event->getRequest();
        $router = $this->container->get('router');

        // extract info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);

        list($app, $module, $method) = $routeParts;
        if ($routeName !== 'graviton.schema.get') {
            list($app, $module, $routeType, $model, $method) = $routeParts;
        }

        $schemaRouteName = 'graviton.schema.get';
        $parameters = array('id' => implode('/', array($module, $model)));

        $schema = sprintf('application/vnd.graviton.schema.%s.%s+json', $module, $model);

        if ($method == 'all') {
            $parameters['id'] = 'schema/collection';
            $schema = 'application/vnd.graviton.schema.collection+json';
        }

        if ($schemaRouteName !== $routeName) {

            $url = $router->generate($schemaRouteName, $parameters, true);

            // append rel=schema link to link headers
            $links = explode(', ', $response->headers->get('Link'));
            $links = array_filter($links);
            $links[] = sprintf('<%s>; rel="schema"; type="%s"', $url, $schema);

            // overwrite link headers with new headers
            $response->headers->set('Link', implode(',', $links));
        }

        $event->setResponse($response);
    }
}
