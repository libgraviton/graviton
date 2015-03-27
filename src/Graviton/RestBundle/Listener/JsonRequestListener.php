<?php
/**
 * ResponseListener for parsing Accept header
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\Container;
use Graviton\RestBundle\Event\RestEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonRequestListener
{
    /**
     * Validate the json input to prevent errors in the following components
     *
     * @param RestEvent $event Event
     *
     * @return void|null
     */
    public function onKernelRequest(RestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->headers->has('Accept')) {

            $format = $request->getFormat($request->headers->get('Accept'));

            if (!empty($format)) {
                $request->setRequestFormat($format);
            }
        }
    }
}
