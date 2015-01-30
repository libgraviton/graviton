<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\RestBundle\Event\RestEvent;

/**
 * FilterResponseListener for setting up CORS headers.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class CorsResponseListener
{
    /**
     * @var string[]
     */
    private $headers = array();

    /**
     * @var string[]
     */
    private $allowHeaders = array('Content-Type', 'Content-Language');

    /**
     * add an allowed header
     *
     * @param string $header header to allow
     *
     * @return void
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    /**
     * add a rel=self Link header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(RestEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $response->headers->set('Access-Control-Allow-Origin', '*');

        $corsMethods = $request->attributes->get('corsMethods', '');
        if (!empty($corsMethods)) {
            $response->headers->set('Access-Control-Allow-Methods', $corsMethods);
        }
        $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->headers));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowHeaders));
    }
}
