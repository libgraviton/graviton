<?php
/**
 * GetResponseListener for parsing Accept-Language headers
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Graviton\ExceptionBundle\Exception\ValidationException;
use Graviton\RestBundle\Event\RestEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NoInputException;

/**
 * GetResponseListener for parsing Accept-Language headers
 *
 * @category RestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidationRequestListener
{
    /**
     * Service container
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * Validate the json input to prevent errors in the following components
     *
     * @param RestEvent|GetResponseEvent $event Event
     *
     * @throws NoInputException
     * @throws ValidationException
     * @throws \Exception
     * @return void
     */
    public function onKernelRequest(RestEvent $event)
    {
        // only validate on POST and PUT
        // if patch is required, refactor the method or do something else
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $controller = $event->getController();

            // Moved this from RestController to ValidationListener (don't know if necessary)
            $content = $event->getRequest()->getContent();
            if (is_resource($content)) {
                throw new \LogicException('unexpected resource in validation');
            }

            // Decode the json from request
            if (!($input = json_decode($content, true))) {
                $e = new NoInputException();
                $e->setResponse($response);
                throw $e;
            }

            // get the input validator
            $inputValidator = $this->container->get("graviton.rest.validation.jsoninput");
            $inputValidator->setRequest($request);

            // get the document manager for this model
            $em = $controller->getModel()->getRepository()->getDocumentManager();
            $inputValidator->setDocumentManager($em);

            // validate the document
            $result = $inputValidator->validate($input, $controller->getModel()->getEntityClass());

            if ($result->count() > 0) {
                // $response->send()...
                $e = new ValidationException("Validation failed");
                $e->setViolations($result);

                // pass the event..???
                $e->setResponse($response);

                throw $e;
            }
        }

        return $event;
    }

    /**
     * Set the container
     *
     * @param \Symfony\Component\DependencyInjection\Container $container Container
     *
     * @return void
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}
