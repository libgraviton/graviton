<?php
/**
 * base listener for rest exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class RestExceptionListener
{
    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor for the RestExceptionlistener
     *
     * @param SerializerInterface $serializer Serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Handle the exception and send the right response
     *
     * @param ExceptionEvent $event Event
     *
     * @return void
     */
    abstract public function onKernelException(ExceptionEvent $event);

    /**
     * Serialize the given content
     *
     * @param mixed $content Content
     *
     * @return string $content Json content
     */
    public function getSerializedContent($content)
    {
        return $this->serializer->serialize(
            $content,
            'json'
        );
    }
}
