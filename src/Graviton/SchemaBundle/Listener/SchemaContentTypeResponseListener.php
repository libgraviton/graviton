<?php
/**
 * Add a Link header to a schema endpoint to a response
 */

namespace Graviton\SchemaBundle\Listener;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Graviton\SchemaBundle\SchemaUtils;

/**
 * Add a Link header to a schema endpoint to a response
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaContentTypeResponseListener
{
    /**
     * @uvar Router
     */
    private $router;

    /**
     * @param router $router router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
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

        $type = $response->headers->get('Content-Type');
        if ($type !== null && substr(strtolower($type), 0, 16) !== 'application/json') {
            return;
        }

        // build content-type string
        $contentType = 'application/json; charset=UTF-8';
        if ($request->get('_route') != 'graviton.core.static.main.all') {
            try {
                $schemaRoute = SchemaUtils::getSchemaRouteName($request->get('_route'));
                $contentType .= sprintf(
                    '; profile=%s',
                    $this->router->generate($schemaRoute, array(), UrlGeneratorInterface::ABSOLUTE_URL)
                );
            } catch (\Exception $e) {
                return true;
            }
        }

        // replace content-type if a schema was requested
        if ($request->attributes->get('schemaRequest')) {
            $contentType = 'application/schema+json';
        }
        $response->headers->set('Content-Type', $contentType);

        $event->setResponse($response);
    }
}
