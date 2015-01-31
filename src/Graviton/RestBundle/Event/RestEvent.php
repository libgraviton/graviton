<?php
namespace Graviton\RestBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Event that is passed to graviton.rest.event listeners
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class RestEvent extends Event
{

    /**
     * Request object
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request = null;

    /**
     * Response object
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $response = null;

    /**
     * Controller which handles the request
     *
     * @var \Graviton\RestBundle\Controller\RestController
     */
    private $controller = null;

    /**
     * Is there a response?
     *
     * @return boolean
     */
    public function hasResponse()
    {
        return null !== $this->response;
    }

    /**
     * Set the request object
     *
     * @param Request $request Request
     *
     * @return \Graviton\RestBundle\Event\RestEvent $this This
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the response object and DON'T stop propagation -> Do this yourself
     * if you need it...
     *
     * @param Response $response Response object
     *
     * @return \Graviton\RestBundle\Event\RestEvent $this This object
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response object
     *
     * @return Response $response Response object
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the controller for this request
     * At the moment, the MainController doesn't extend the RestController.
     * As soon this is refactored, we can add a type hint to the method
     *
     * @param RestController $controller Controller
     *
     * @return \Graviton\RestBundle\Event\RestEvent $this This object
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get the controller
     *
     * @return RestController $controller Controller
     */
    public function getController()
    {
        return $this->controller;
    }
}
