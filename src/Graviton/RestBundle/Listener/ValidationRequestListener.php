<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Graviton\ExceptionBundle\Exception\ValidationException;
use Graviton\RestBundle\Event\RestEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * GetResponseListener for parsing Accept-Language headers
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ValidationRequestListener
{
    /**
     * Service container
     *
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * Validate the json input to prevent errors in the following components
     *
     * @param GetResponseEvent $event Event
     *
     * @return void
     */
    public function onKernelRequest(RestEvent $event)
    {
        // only validate on POST and PUT
        // if patch is required, refactor the method or do something else
        $request = $event->getRequest();

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
        	$controller = $event->getController();
        	
            // get the input validator
            $inputValidator = $this->container->get("graviton.rest.validation.jsoninput");

            // get the document manager for this model
            $em = $controller->getModel()->getRepository()->getDocumentManager();
            $inputValidator->setDocumentManager($em);

            // Moved this from RestController to ValidationListener (don't know if necessary)
            $content = $event->getRequest()->getContent();
            if (is_resource($content)) {
                throw new \LogicException('unexpected resource in validation');
            }

            // Decode the json from request
            $input = json_decode($content, true);

            // validate the document
            $result = $inputValidator->validate($input, $controller->getModel()->getEntityClass());

            if ($result->count() > 0) {
                // $response->send()...
                $e = new ValidationException("Validation failed");
                $e->setViolations($result);
                
                $response = $event->getResponse()->setStatusCode(Response::HTTP_BAD_REQUEST);
                
                // pass the event..???
                $e->setResponse($event->getResponse());

                throw $e;
            }
        }

        return $event;
    }

    /**
     * Set the container
     *
     * @param Symfony\Component\DependencyInjection\Container $container Container
     *
     * @return void
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}
