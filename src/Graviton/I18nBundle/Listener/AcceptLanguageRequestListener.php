<?php
/**
 * GetResponseListener for parsing Accept-Language headers
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18nBundle\Service\I18nCacheUtils;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\AcceptHeader;

/**
 * GetResponseListener for parsing Accept-Language headers
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AcceptLanguageRequestListener
{

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var I18nCacheUtils
     */
    private $cacheUtils;

    /**
     * set language repository used for getting available languages
     *
     * @param string         $defaultLocale default locale to return if no language given in request
     * @param I18nCacheUtils $cacheUtils    i18n cache utils
     */
    public function __construct(
        $defaultLocale,
        I18nCacheUtils $cacheUtils
    ) {
        $this->defaultLocale = $defaultLocale;
        $this->cacheUtils = $cacheUtils;
    }

    /**
     * parse Accept-Language header from request.
     *
     * @param GetResponseEvent $event listener event
     *
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $headers = AcceptHeader::fromString($event->getRequest()->headers->get('Accept-Language'));
        $defaultLanguage = [$this->defaultLocale => $this->defaultLocale];

        $languages = array_intersect(
            array_map(
                function ($header) {
                    return $header->getValue();
                },
                $headers->all()
            ),
            $this->cacheUtils->getLanguages()
        );

        $languages = array_unique(
            array_merge($defaultLanguage, $languages)
        );

        $event->getRequest()->attributes->set('languages', $languages);
    }
}
