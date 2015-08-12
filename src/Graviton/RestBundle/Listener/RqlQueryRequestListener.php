<?php
/**
 * RqlQueryRequestListener class file
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RqlParserBundle\Listener\RequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * RQL query listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlQueryRequestListener extends RequestListener
{
    /**
     * @var array Allowed route IDs
     */
    private $allowedRoutes = [];
    /**
     * @var RequestListener
     */
    private $requestListener;

    /**
     * Constructor
     *
     * @param RequestListener $requestListener Original RQL listener
     * @param array           $allowedRoutes   Allowed route IDs
     */
    public function __construct(RequestListener $requestListener, array $allowedRoutes)
    {
        $this->requestListener = $requestListener;
        $this->allowedRoutes = $allowedRoutes;
    }

    /**
     * Process RQL query if it is allowed for current route
     *
     * @param GetResponseEvent $event Event
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!in_array($event->getRequest()->attributes->get('_route'), $this->allowedRoutes, true)) {
            return;
        }

        $this->requestListener->onKernelRequest($event);
    }
}
