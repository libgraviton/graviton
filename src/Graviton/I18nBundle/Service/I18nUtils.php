<?php
/**
 * service for i18n stuff
 */

namespace Graviton\I18nBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\DocumentBundle\Entity\Translatable;
use Graviton\I18nBundle\Document\Translation;
use Graviton\I18nBundle\Translator\Translator;

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
     * @var DocumentManager
     */
    private $manager;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * Constructor
     *
     * @param DocumentManager $manager    manager
     * @param Translator      $translator Translator
     */
    public function __construct(
        DocumentManager $manager,
        Translator $translator
    ) {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /**
     * Returns the default/original language
     *
     * @return string default language
     */
    public function getDefaultLanguage()
    {
        return $this->translator->getDefaultLanguage();
    }

    /**
     * Returns all languages as a simple flat array
     *
     * @return array array of language id's
     */
    public function getLanguages()
    {
        return $this->translator->getLanguages();
    }

    /**
     * build a complete translated field
     *
     * @param string $value value to translate
     *
     * @return array array with translated strings
     */
    public function getTranslatedField($value)
    {
        return $this->translator->translate($value);
    }

    /**
     * persists a translatable entity
     *
     * @param Translatable $translatable translatable
     *
     * @return void
     */
    public function persistTranslatable(Translatable $translatable)
    {
        $this->translator->persistTranslatable($translatable);
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
        $builder = $this->manager->createQueryBuilder(Translation::class);
        $builder
            ->field('language')->equals($sourceLocale);

        if ($useWildCard === true) {
            $value = new \MongoRegex('/'.$value.'/');
        }

        /*
         * we have 2 cases to match
         * - 'translated' is set and matches
         * - 'translated' is not present, so 'original' can match (as this is inserted 'virtually')
         */
        $builder->addAnd(
            $builder->expr()
                ->addOr(
                    $builder->expr()->field('localized')->equals($value)
                )
                ->addOr(
                    $builder->expr()
                        ->field('localized')->equals(null)
                        ->field('original')->equals($value)
                )
        );

        $query = $builder->getQuery();

        return $query->execute()->toArray();
    }
}
