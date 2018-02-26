<?php
/**
 * GetResponseListener for parsing Accept-Language headers
 */

namespace Graviton\I18nBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Graviton\I18nBundle\Repository\LanguageRepository;

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
     * @var LanguageRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * set language repository used for getting available languages
     *
     * @param LanguageRepository $repository    repo
     * @param string             $defaultLocale default locale to return if no language given in request
     */
    public function __construct(LanguageRepository $repository, $defaultLocale)
    {
        $this->repository = $repository;
        $this->defaultLocale = $defaultLocale;
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
        $request = $event->getRequest();
        $headers = AcceptHeader::fromString($request->headers->get('Accept-Language'));

        $defaultLanguage = [$this->defaultLocale => $this->defaultLocale];

        $languages = array_intersect(
            array_map(
                function ($header) {
                    return $header->getValue();
                },
                $headers->all()
            ),
            array_map(
                function ($language) {
                    return $language->getId();
                },
                $this->repository->findAll()
            )
        );

        $languages = array_unique(
            array_merge($defaultLanguage, $languages)
        );

        $request->attributes->set('languages', $languages);
    }
}
