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

        // extract info from route
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);

        list($app, $module, $method) = $routeParts;
        $model = 'stdClass';
        if ($routeName !== 'graviton.schema.get') {
            list($app, $module, , $model, $method) = $routeParts;
        }

        // build content-type string
        $contentType = sprintf('application/vnd.%s.%s.%s+json', $app, $module, $model);
        if ($method == 'all') {
            $contentType = 'application/vnd.graviton.schema.collection+json';
        }

        if ($routeName !== 'graviton.schema.get') {
            $response->headers->set('Content-Type', $contentType.'; charset=UTF-8');
        }

        $event->setResponse($response);
    }
}
