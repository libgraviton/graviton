<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\RestBundle\Event\RestEvent;

/**
 * FilterResponseListener for setting up CORS headers.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
    }
}
