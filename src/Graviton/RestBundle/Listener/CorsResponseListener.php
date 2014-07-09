<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for setting up CORS headers.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
    public function addheader($header)
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
    public function onKernelResponse(FilterResponseEvent $event)
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

        $event->setResponse($response);
    }
}
