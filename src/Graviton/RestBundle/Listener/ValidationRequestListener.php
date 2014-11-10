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
            list ($serviceName, $action) = explode(":", $event->getRequest()->get('_controller'));

            $controller = $this->container->get($serviceName);
            $inputValidator = $this->container->get("graviton.rest.validation.jsoninput");
            $serializer = $this->container->get('graviton.rest.serializer');
            $serializerContext = clone $this->container->get('graviton.rest.serializer.serializercontext');

            // Moved this from RestController to ValidationListener (don't know if necessary)
            $content = $event->getRequest()->getContent();
            if (is_resource($content)) {
                throw new \LogicException('unexpected resource in validation');
            }

            // Decode the json from request
            $input = json_decode($content, true);
            $result = $inputValidator->validate($input, $controller->getModel());

            if ($result->count() > 0) {
                // Hmpf... isn't it possible to send the response right now and
                // stop execution of the stack???
                //$event->setResponse($response); This only stops the request event...

                // Create a "ValidationException" class and catch the error
                // later in an errorhandler
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
