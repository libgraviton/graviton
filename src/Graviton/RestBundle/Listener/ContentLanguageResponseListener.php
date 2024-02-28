<?php
/**
 * ContentLanguageResponseListener
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RestBundle\Service\I18nUtils;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
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
     * @param ResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->set(
            'Content-Language',
            implode(', ', $this->utils->getLanguages())
        );
    }
}
