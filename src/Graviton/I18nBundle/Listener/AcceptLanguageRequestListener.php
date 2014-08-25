<?php

namespace Graviton\I18nBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Graviton\I18nBundle\Repository\LanguageRepository;

/**
 * GetResponseListener for parsing Accept-Language headers
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AcceptLanguageRequestListener
{
    /**
     * @var Graviton\I18nBundle\Repository\LanguageRepository
     */
    private $repository;

    /**
     * set language repository used for getting available languages
     *
     * @param LanguageRepository $repository repo
     *
     * @return void
     */
    public function setRepository(LanguageRepository $repository)
    {
        $this->repository = $repository;
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

        $request->attributes->set('languages', $languages);

    }
}
