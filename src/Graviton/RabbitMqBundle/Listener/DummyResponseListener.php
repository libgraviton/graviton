<?php
/**
 * dummy listener to activate in config_test.yml to not slow down our unit tests.
 * if not, every post/put/delete requests in the tests lead to lookups in the mongodb and
 * (if subscribed to them) output on the queue
 */

namespace Graviton\RabbitMqBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DummyResponseListener
{

    /**
     * dummy event
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        return;
    }
}
