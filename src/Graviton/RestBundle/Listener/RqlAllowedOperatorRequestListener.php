<?php
/**
 * RqlAllowedOperatorRequestListener class file
 */

namespace Graviton\RestBundle\Listener;

use Graviton\ExceptionBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\RestBundle\Event\RestEvent;
use Graviton\RqlParser\AbstractNode;

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
     * @param RestEvent $event Event
     * @return void
     */
    public function onKernelRequest(RestEvent $event)
    {
        $rqlQuery = $event->getRequest()->attributes->get('rqlQuery');
        if ($rqlQuery === null) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');
        if (substr($route, -4) !== '.get') {
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
