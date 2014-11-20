<?php
namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\DependencyInjection\Container;

/**
 * Listener for validation exceptions
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
abstract class RestExceptionListener
{
    /**
     * Service container
     *
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * Handle the exception and send the right response
     *
     * @param GetResponseForExceptionEvent $event Event
     *
     * @return void
     */
    abstract public function onKernelException(GetResponseForExceptionEvent $event);

    /**
     * Set the DI container
     *
     * @param Symfony\Component\DependencyInjection\Container $container DI container
     *
     * @return \Graviton\ExceptionBundle\Listener\RestExceptionListener
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the DI container
     *
     * @return Symfony\Component\DependencyInjection\Container
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
        $serializerContext = clone $this->getContainer()->get('graviton.rest.serializer.serializercontext');

        return $serializer->serialize(
            $content,
            'json',
            $serializerContext
        );
    }
}
