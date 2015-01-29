<?php

namespace Graviton\I18nBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding Content-Lanugage headers
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ContentLanguageResponseListener
{
    /**
     * add a rel=self Link header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $languages = $event->getRequest()->attributes->get('languages');
        $response = $event->getResponse();
        $response->headers->set('Content-Language', implode(', ', $languages));
        $event->setResponse($response);
    }
}
