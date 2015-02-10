<?php
/**
 * base listener for rest exceptions
 *
 * PHP Version 5
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base listener for rest exceptions
 *
 * There are multiple handler classes. Each handles a specific exception.
 * At the moment, these classes only prepare the response before sending it.
 * Feel free to add more functionality to these handlers (e.g. logging).
 *
 * All these handlers call setResponse() on the event object. When doing
 * this, the whole response-event-stack (kernel.response, graviton.rest.response
 * and all the response listeners you added in your own bundles) is processed.
 * If this is not the behaviour you expect, you can send your response directly to
 * the client.
 * Have a look at Symfony\Component\HttpFoundation\Response or Symfony\Component\HttpFoundation\JsonRepsonse
 * to find out how this works
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
abstract class RestExceptionListener
{
    /**
     * Service container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Handle the exception and send the right response
     *
     * @param GetResponseForExceptionEvent $event Event
     *
     * @return void
     */
    abstract public function onKernelException(\GetResponseForExceptionEvent $event);

    /**
     * Set the DI container
     *
     * @param ContainerInterface $container DI container
     *
     * @return RestExceptionListener
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the DI container
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Serialize the given content
     *
     * @param mixed $content Content
     *
     * @return string $content Json content
     */
    public function getSerializedContent($content)
    {
        $serializer = $this->getContainer()->get('graviton.rest.serializer');

        // can't use the same context twice.. maybe scope="prototype" in service.xml would do the trick
        $serializerContext = clone $this->getContainer()->get('graviton.rest.serializer.serializercontext');

        return $serializer->serialize(
            $content,
            'json',
            $serializerContext
        );
    }
}
