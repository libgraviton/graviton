<?php

namespace Graviton\I18nBundle\Listener;

use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use Symfony\Component\HttpFoundation\Request;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\I18nBundle\Document\Translatable;
use Graviton\I18nBundle\Model\Translatable as TranslatableModel;

/**
 * translate fields during serialization
 *
 * @category I18nBundle
 * @package  Graviton
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
     * remove translateable strings from object
     *
     * @param PreDeserializeEvent $event event
     *
     * @return void
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $eventClass = $event->getType()['name'];
        $object = new $eventClass;
        if ($object instanceof TranslatableDocumentInterface) {
            $data = $event->getData();

            foreach ($object->getTranslatableFields() as $field) {
                if (isset($data[$field])) {
                    $this->localizedFields[$field] = $data[$field];
                    $defaultValue = \reset($data[$field]);
                    if (array_key_exists('en', $data[$field])) {
                        $defaultValue = $data[$field]['en'];
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
        if (!array_key_exists('en', $values)) {
            throw new \Exception('Creating new trans strings w/o en is not support yet.');
            // @todo generate convention based keys instead of excepting
        }
        $original = $values['en'];
        // @todo change this so it grabs all languages and not negotiated ones
        if (isset($this->request)) {
            $languages = $this->request->attributes->get('languages');
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
                    $translatable->setDomain('i18n');
                    $translatable->setOriginal($original);
                    $translatable->setTranslated($translated);
                    $translatable->setIsLocalized($isLocalized);
                    $this->translatables->insertRecord($translatable);
                }
            );
        }
    }
}
