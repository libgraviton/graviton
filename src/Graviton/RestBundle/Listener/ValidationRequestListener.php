<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Graviton\ExceptionBundle\Exception\ValidationException;

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
    public function onKernelRequest(GetResponseEvent $event)
    {
        // only validate on POST and PUT
        // if patch is required, refactor the method or do something else
        $request = $event->getRequest();

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            // get the service name
            list ($serviceName, $action) = explode(":", $event->getRequest()->get('_controller'));

            // get the controller which handles this request           
            $controller = $this->container->get($serviceName);
            
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

                if (($event->hasResponse())) {
                    $e->setResponse($event->getResponse());
                }

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
