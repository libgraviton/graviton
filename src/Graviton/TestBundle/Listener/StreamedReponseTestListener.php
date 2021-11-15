<?php
/**
 * listener that works around how StreamedResponses work in order
 * to test them.. thanks to https://github.com/symfony/symfony/issues/25005#issuecomment-693049282
 */

namespace Graviton\TestBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class StreamedReponseTestListener
{

    /**
     * convert streamedresponse response into a normal one
     *
     * @param ResponseEvent $event event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        if ($response instanceof StreamedResponse) {
            // Buffer output
            ob_start();

            // Output response contents
            $response->send();

            // Get and clean output buffer contents
            $contents = ob_get_clean();

            // Return normal response
            $event->setResponse(
                new Response($contents, $response->getStatusCode(), $response->headers->all())
            );
        }
    }
}
