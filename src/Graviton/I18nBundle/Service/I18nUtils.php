<?php
/**
 * service for i18n stuff
 */

namespace Graviton\I18nBundle\Service;

use Graviton\I18nBundle\Model\Translatable;
use Graviton\I18nBundle\Document\Translatable as TranslatableDocument;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

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

    /**
     * Constructor
     *
     * @param string              $defaultLanguage    default language
     * @param TranslatorInterface $translator         Translator
     * @param Translatable        $translatable       translatable
     * @param LanguageRepository  $languageRepository lang repo
     * @param Request             $request            request
     */
    public function __construct(
        $defaultLanguage,
        TranslatorInterface $translator,
        Translatable $translatable,
        LanguageRepository $languageRepository,
        Request $request = null
    ) {
        $this->defaultLanguage = $defaultLanguage;
        $this->translator = $translator;
        $this->translatable = $translatable;
        $this->languageRepository = $languageRepository;
        $this->request = $request;
    }

    /**
     * Returns whether we are in a Translatable context. That means if we can determine a translation domain.
     *
     * @return bool true if yes, false if not
     */
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

    /**
     * Returns the default/original language - is set by DIC param
     *
     * @return string default language
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * Returns all languages as a simple flat array
     *
     * @return array array of language id's
     */
    public function getLanguages()
    {
        $languages = array();
        foreach ($this->languageRepository->findAll() as $lang) {
            $languages[] = $lang->getId();
        }
        return $languages;
    }

    /**
     * build a complete translated field
     *
     * @param string $value     value to translate
     * @param bool   $forClient if true, we look at languages header, false we render all languages
     *
     * @return array array with translated strings
     */
    public function getTranslatedField($value, $forClient = true)
    {
        $domain = $this->getTranslatableDomain();

        if ($forClient) {
            $languages = $this->request->attributes->get('languages');
        } else {
            $languages = $this->getLanguages();
        }

        return array_map(
            function ($language) use ($value, $domain) {
                return $this->translator->trans($value, array(), $domain, $language);
            },
            $languages
        );
    }

    /**
     * [In|Up]serts a Translatable object using an array with language strings.
     *
     * @param array $values array with language strings; key should be language id
     * @throws \Exception
     *
     * @return void
     */
    public function insertTranslatable(array $values)
    {
        if (!isset($values[$this->getDefaultLanguage()])) {
            throw new \Exception(
                sprintf(
                    'Creating new Translatable without "%s" key is not support yet.',
                    $this->getDefaultLanguage()
                )
            );
        }

        $original = $values[$this->getDefaultLanguage()];

        if ($this->isTranslatableContext()) {
            $languages = $this->getLanguages();
            \array_walk(
                $languages,
                function ($locale) use ($original, $values) {
                    $isLocalized = false;
                    $translated = '';
                    if (array_key_exists($locale, $values)) {
                        $translated = $values[$locale];
                        $isLocalized = true;
                    }
                    $translatable = new TranslatableDocument();
                    $translatable->setId('i18n-' . $locale . '-' . $original);
                    $translatable->setLocale($locale);
                    $translatable->setDomain($this->getTranslatableDomain());
                    $translatable->setOriginal($original);
                    $translatable->setTranslated($translated);
                    $translatable->setIsLocalized($isLocalized);
                    $this->translatable->insertRecord($translatable);
                }
            );
        }
    }
}
