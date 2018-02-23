<?php
/**
 * ResponseListener for parsing Accept header
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\DependencyInjection\Container;
use Graviton\RestBundle\Event\RestEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SpecialMimetypeRequestListener
{
    /**
     * Service container
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;


    /**
     * @param \Symfony\Component\DependencyInjection\Container $container Container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * Adds the configured formats and mimetypes to the request.
     *
     * @param RestEvent $event Event
     *
     * @return void|null
     */
    public function onKernelRequest(RestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->headers->has('Accept')) {
            $format = $request->getFormat($request->headers->get('Accept'));
            if (empty($format)) {
                foreach ($this->container->getParameter('graviton.rest.special_mimetypes') as $format => $types) {
                    $mimetypes = $request->getMimeType($format);

                    if (!empty($mimetypes)) {
                        $mimetypes = is_array($mimetypes)? $mimetypes : array($mimetypes);
                        $types = array_unique(array_merge_recursive($mimetypes, $types));
                    }

                    $request->setFormat($format, $types);
                }
            }
        }
    }
}
