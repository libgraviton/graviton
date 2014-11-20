<?php
namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Graviton\ExceptionBundle\Exception\DeserializationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for deserialization exceptions
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
