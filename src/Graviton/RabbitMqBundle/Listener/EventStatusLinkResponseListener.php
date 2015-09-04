<?php
/**
 * Response listener that adds an eventStatus to Link header if necessary
 */

namespace Graviton\RabbitMqBundle\Listener;

use Graviton\RabbitMqBundle\Document\QueueEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;

/**
 * Response listener that adds an eventStatus to Link header if necessary
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EventStatusLinkResponseListener
{

    /**
     * add a rel=eventStatus Link header to the response if necessary
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($request->attributes->get('eventStatus') instanceof QueueEvent) {
            $linkHeader = LinkHeader::fromResponse($response);
            $linkHeader->add(
                new LinkHeaderItem(
                    $request->attributes->get('eventStatus')->getStatusurl(),
                    array('rel' => 'eventStatus')
                )
            );

            $response->headers->set(
                'Link',
                (string) $linkHeader
            );
        }
    }
}
