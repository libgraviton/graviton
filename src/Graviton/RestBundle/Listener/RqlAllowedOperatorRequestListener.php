<?php
/**
 * RqlAllowedOperatorRequestListener class file
 */

namespace Graviton\RestBundle\Listener;

use Graviton\ExceptionBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\RqlParser\AbstractNode;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * RQL allowed operators listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlAllowedOperatorRequestListener
{
    /**
     * Process RQL query if it is allowed for current route
     *
     * @param RequestEvent $event Event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $rqlQuery = $event->getRequest()->attributes->get('rqlQuery');
        if ($rqlQuery === null) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');
        if (!str_ends_with($route, '.get')) {
            return;
        }

        foreach (['getQuery', 'getSort', 'getLimit'] as $method) {
            /** @var AbstractNode $node */
            $node = $rqlQuery->$method();
            if ($node === null) {
                continue;
            }

            throw new RqlOperatorNotAllowedException($node->getNodeName());
        }
    }
}
