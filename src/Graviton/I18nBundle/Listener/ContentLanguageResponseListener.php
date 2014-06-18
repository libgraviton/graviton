<?php

namespace Graviton\I18nBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Graviton\I18nBundle\Repository\LanguageRepository;

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
     * @var Graviton\I18nBundle\Repository\LanguageRepository;
     */
    private $repository;

    /**
     * set language repository used for getting available languages
     *
     * @param Graviton\I18nBundle\Repository\LanguageRepository $repository repo
     *
     * @return void
     */
    public function setRepository(LanguageRepository $repository)
    {
        $this->repository = $repository;
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
        $languages = array_map(
            function ($language) {
                return $language->getId();
            },
            $this->repository->findAll()
        );
        $tags = array_intersect(
            $languages,
            $event->getRequest()->attributes->get('languages')
        );

        $response = $event->getResponse();
        $response->headers->set('Content-Language', implode(', ', $tags));
        $event->setResponse($response);
    }
}
