<?php
/**
 * Listener for deserialization exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Graviton\ExceptionBundle\Exception\DeserializationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for deserialization exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DeserializationExceptionListener extends RestExceptionListener
{
    /**
     * Handle the exception and send the right response
     *
     * @param GetResponseForExceptionEvent $event Event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // var_dump does not work here... use
        //    \Doctrine\Common\Util\Debug::dump($e);die;
        if (($exception = $event->getException()) instanceof DeserializationException) {
            // hnmm.. no way to find out which property (name) failed??
            $msg = array('message' => $exception->getPrevious()->getMessage());

            $response = $exception->getResponse();
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(
                $this->getSerializedContent($msg)
            );

            $event->setResponse($response);
        }
    }
}
