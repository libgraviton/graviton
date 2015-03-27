<?php
/**
 * translate fields during serialization
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18NBundle\Service\I18NUtils;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use Symfony\Component\HttpFoundation\Request;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\I18nBundle\Document\Translatable;
use Graviton\I18nBundle\Model\Translatable as TranslatableModel;

/**
 * translate fields during serialization
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class I18nDeserializationListener
{
    /**
     * @var mixed[]
     */
    protected $localizedFields = array();

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Graviton\I18nBundle\Model\Translatable
     */
    private $translatables;

    /**
     * @var \Graviton\I18nBundle\Service\I18nUtils
     */
    protected $i18nUtils;

    /**
     * set request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request request object
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * setup storage for translatable strings
     *
     * @param \Graviton\I18nBundle\Model\Translatable $translatables model
     *
     * @return void
     */
    public function setTranslatables(TranslatableModel $translatables)
    {
        $this->translatables = $translatables;
    }

    /**
     * set utils
     *
     * @param \Graviton\I18NBundle\Service\I18NUtils $i18nUtils utils
     *
     * @return void
     */
    public function setI18nUtils(I18NUtils $i18nUtils)
    {
        $this->i18nUtils = $i18nUtils;
    }

    /**
     * remove translateable strings from object
     *
     * @param PreDeserializeEvent $event event
     *
     * @return void
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $eventClass = $event->getType()['name'];
        $defaultLanguage = $this->i18nUtils->getDefaultLanguage();
        $object = new $eventClass;
        if ($object instanceof TranslatableDocumentInterface) {
            $data = $event->getData();

            foreach ($object->getTranslatableFields() as $field) {
                if (isset($data[$field])) {
                    $this->localizedFields[$field] = $data[$field];
                    $defaultValue = \reset($data[$field]);
                    if (array_key_exists($defaultLanguage, $data[$field])) {
                        $defaultValue = $data[$field][$defaultLanguage];
                    }
                    $data[$field] = $defaultValue;
                }
            }
            $event->setData($data);
        }
    }

    /**
     * translate all strings marked as multi lang
     *
     * @return void
     */
    public function onPostDeserialize()
    {
        \array_walk(
            $this->localizedFields,
            function ($values) {
                $this->createTranslatables($values);
            }
        );
    }

    /**
     * create translatables for all the given languages
     *
     * @param string[] $values values for multiple languages
     *
     * @return void
     */
    public function createTranslatables($values)
    {
        if (!array_key_exists($this->i18nUtils->getDefaultLanguage(), $values)) {
            throw new \Exception(
                sprintf(
                    'Creating new trans strings w/o "%s" is not support yet.',
                    $this->i18nUtils->getDefaultLanguage()
                )
            );
            // @todo generate convention based keys instead of excepting
        }

        $original = $values['en'];
        // @todo change this so it grabs all languages and not negotiated ones
        if ($this->i18nUtils->isTranslatableContext()) {
            $languages = $this->i18nUtils->getLanguages();
            \array_walk(
                $languages,
                function ($locale) use ($original, $values) {
                    $isLocalized = false;
                    $translated = '';
                    if (array_key_exists($locale, $values)) {
                        $translated = $values[$locale];
                        $isLocalized = true;
                    }
                    $translatable = new Translatable;
                    $translatable->setId('i18n-' . $locale . '-' . $original);
                    $translatable->setLocale($locale);
                    $translatable->setDomain($this->i18nUtils->getTranslatableDomain());
                    $translatable->setOriginal($original);
                    $translatable->setTranslated($translated);
                    $translatable->setIsLocalized($isLocalized);
                    $this->translatables->insertRecord($translatable);
                }
            );
        }
    }
}
