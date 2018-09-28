<?php
/**
 * FilterResponseListener for adding Content-Lanugage headers
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18nBundle\Service\I18nUtils;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for adding Content-Lanugage headers
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ContentLanguageResponseListener
{

    /**
     * @var I18nUtils
     */
    private $utils;

    /**
     * ContentLanguageResponseListener constructor.
     *
     * @param I18nUtils $utils utils
     */
    public function __construct(I18nUtils $utils)
    {
        $this->utils = $utils;
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
        $event->getResponse()->headers->set(
            'Content-Language',
            implode(', ', $this->utils->getLanguages())
        );
    }
}
