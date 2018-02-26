<?php
/**
 * service for i18n stuff
 */

namespace Graviton\I18nBundle\Service;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\I18nBundle\Model\Translatable;
use Graviton\I18nBundle\Document\Translatable as TranslatableDocument;
use Graviton\I18nBundle\Document\TranslatableLanguage;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * A service (meaning symfony service) providing some convenience stuff when dealing with our RestController
 * based services (meaning rest services).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @var array
     */
    protected $languages = [];

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
        if (empty($this->languages)) {
            foreach ($this->languageRepository->findAll() as $lang) {
                $this->languages[] = $lang->getId();
            }
        }
        return $this->languages;
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
     * This function allows to search for existing translations from a source
     * language, probably using a wildcard
     *
     * @param string  $value        the translated string
     * @param string  $sourceLocale a source locale
     * @param boolean $useWildCard  if we should search wildcard or not
     *
     * @return array matching Translatables
     */
    public function findMatchingTranslatables($value, $sourceLocale, $useWildCard = false)
    {
        // i need to use a queryBuilder as the repository doesn't let me do regex queries (i guess so..)
        $builder = $this->translatable->getRepository()->createQueryBuilder();
        $builder
            ->field('domain')->equals($this->getTranslatableDomain())
            ->field('locale')->equals($sourceLocale);

        if ($useWildCard === true) {
            $value = new \MongoRegex($value);
        }

        /*
         * we have 2 cases to match
         * - 'translated' is set and matches
         * - 'translated' is not present, so 'original' can match (as this is inserted 'virtually')
         */
        $builder->addAnd(
            $builder->expr()
                ->addOr(
                    $builder->expr()->field('translated')->equals($value)
                )
                ->addOr(
                    $builder->expr()
                        ->field('translated')->equals(null)
                        ->field('original')->equals($value)
                )
        );

        $query = $builder->getQuery();

        return $query->execute()->toArray();
    }

    /**
     * Flush the translatables if it hasn't been done yet
     *
     * @return void
     */
    public function flushTranslatables()
    {
        $this->translatable->flush();
    }

    /**
     * [In|Up]serts a Translatable object using an array with language strings.
     *
     * @param array $values  array with language strings; key should be language id
     * @param bool  $doFlush if we should flush after the insert or not
     * @throws \Exception
     *
     * @return void
     */
    public function insertTranslatable(array $values, $doFlush = true)
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
                function ($locale) use ($original, $values, $doFlush) {
                    $isLocalized = false;
                    $translated = '';
                    $domain = $this->getTranslatableDomain();
                    if (array_key_exists($locale, $values)) {
                        $translated = $values[$locale];
                        $isLocalized = true;
                    }
                    $translatable = new TranslatableDocument();
                    $translatable->setId($domain . '-' . $locale . '-' . $original);
                    $translatable->setLocale($locale);
                    $translatable->setDomain($domain);
                    $translatable->setOriginal($original);
                    $translatable->setTranslated($translated);
                    $translatable->setIsLocalized($isLocalized);
                    $translatableLang = new TranslatableLanguage();
                    $translatableLang->setRef(ExtReference::create('Language', $locale));
                    $translatable->setLanguage($translatableLang);
                    $this->translatable->insertRecord($translatable, false, $doFlush);
                }
            );
        }
    }
}
