<?php
/**
 * translate fields during serialization
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\ExceptionBundle\Exception\DeserializationException;
use Graviton\I18NBundle\Service\I18nUtils;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

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
     * @var \Graviton\I18nBundle\Service\I18nUtils
     */
    protected $utils;

    /**
     * set utils (i18nutils)
     *
     * @param \Graviton\I18NBundle\Service\I18NUtils $utils utils
     *
     * @return void
     */
    public function setUtils(I18nUtils $utils)
    {
        $this->utils = $utils;
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

        if (!class_exists($eventClass)) {
            throw new DeserializationException(sprintf('Can\' find class %s to deserialize', $eventClass));
        }

        $object = new $eventClass;

        if ($object instanceof TranslatableDocumentInterface) {
            $data = $event->getData();

            foreach ($object->getTranslatableFields() as $field) {
                $isArray = substr($field, -2, 2) === '[]';
                if ($isArray) {
                    $field = substr($field, 0, -2);
                }

                if (!isset($data[$field])) {
                    continue;
                }

                if ($isArray) {
                    $this->localizedFields = array_merge($this->localizedFields, array_values($data[$field]));
                    $data[$field] = array_map([$this, 'getDefaultTranslation'], array_values($data[$field]));
                } else {
                    $this->localizedFields[] = $data[$field];
                    $data[$field] = $this->getDefaultTranslation($data[$field]);
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
                $this->utils->insertTranslatable($values);
            }
        );
    }

    /**
     * Get default translation
     *
     * @param array $translations Translations
     * @return string
     */
    private function getDefaultTranslation(array $translations)
    {
        $defaultLanguage = $this->utils->getDefaultLanguage();
        return isset($translations[$defaultLanguage]) ? $translations[$defaultLanguage] : reset($translations);
    }
}
