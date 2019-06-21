<?php
/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 */

namespace Graviton\SchemaBundle\Listener;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\LinkHeaderParser\LinkHeaderItem;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Graviton\SchemaBundle\SchemaUtils;

/**
 * FilterResponseListener for adding a rel=self Link header to a response.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CanonicalSchemaLinkResponseListener
{
    /**
     * @var Router $router
     */
    private $router;

    /**
     * @param Router $router router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
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
        $request = $event->getRequest();
        if ($request->attributes->get('schemaRequest', false)) {
            $response = $event->getResponse();
            $linkHeader = LinkHeader::fromString($response->headers->get('Link', null));

            $routeName = SchemaUtils::getSchemaRouteName($request->get('_route'));
            $url = $this->router->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL);

            // append rel=canonical link to link headers
            $linkHeader->add(new LinkHeaderItem($url, 'canonical'));

            // overwrite link headers with new headers
            $response->headers->set('Link', (string) $linkHeader);

            $event->setResponse($response);
        }
    }
}
