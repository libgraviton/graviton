<?php
/**
 * service for i18n stuff
 */

namespace Graviton\I18nBundle\Service;

use Graviton\I18nBundle\Model\Translatable;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * A service (meaning symfony service) providing some convenience stuff when dealing with our RestController
 * based services (meaning rest services).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class I18nUtils
{

    /**
     * @var string
     */
    protected $defaultLanguage;

    /**
     * @var \Graviton\I18nBundle\Model\Translatable
     */
    protected $translatable;

    /**
     * @var \Graviton\I18nBundle\Repository\LanguageRepository
     */
    protected $languageRepository;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function __construct($defaultLanguage, Translatable $translatable, LanguageRepository $languageRepository, Request $request = null)
    {
        $this->defaultLanguage = $defaultLanguage;
        $this->translatable = $translatable;
        $this->languageRepository = $languageRepository;
        $this->request = $request;
    }

    public function isTranslatableContext()
    {
        return (!is_null($this->getTranslatableDomain()));
    }

    /**
     * Returns the domain to use according to the current request.
     * If there is no valid request, null will be returned..
     *
     * @return string domain
     */
    public function getTranslatableDomain()
    {
        $ret = null;
        if ($this->request instanceof Request) {
            $uriParts = explode('/', substr($this->request->getRequestUri(), 1));
            if (isset($uriParts[0])) {
                $ret = $uriParts[0];
            }
        }
        return $ret;
    }

    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    public function getLanguages()
    {
        $languages = array();
        foreach ($this->languageRepository->findAll() as $lang) {
            $languages[] = $lang->getId();
        }
        return $languages;
    }

    public function insertTranslatable()
    {

    }




}
