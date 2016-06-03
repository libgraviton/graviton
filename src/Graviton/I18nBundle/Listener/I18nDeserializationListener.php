<?php
/**
 * translate fields during serialization
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\ExceptionBundle\Exception\DeserializationException;
use Graviton\I18NBundle\Service\I18nUtils;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

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
    protected $translatableStore = [];

    /**
     * @var \Graviton\I18nBundle\Service\I18nUtils
     */
    protected $utils;

    protected $defaultLanguage = null;

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
        $this->defaultLanguage = $utils->getDefaultLanguage();
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
                    $this->queueTranslatable($data[$field], true);
                    $data[$field] = array_map([$this, 'getDefaultTranslation'], array_values($data[$field]));
                } else {
                    $this->queueTranslatable($data[$field]);
                    $data[$field] = $this->getDefaultTranslation($data[$field]);
                }
            }
            $event->setData($data);
        }
    }

    public function onKernelFinishRequest(KernelEvent $event)
    {
        foreach ($this->translatableStore as $translatable) {
            $this->utils->insertTranslatable($translatable, false);
        }
        $this->utils->flushTranslatables();
    }

    private function queueTranslatable(array $translatable, $isArray = false)
    {
        if (!$isArray) {
            $translatable = [$translatable];
        }

        foreach ($translatable as $singleItem) {
            $this->translatableStore[$singleItem[$this->defaultLanguage]] = $singleItem;
        }
    }

    /**
     * Get default translation
     *
     * @param array $translations Translations
     * @return string
     */
    private function getDefaultTranslation(array $translations)
    {
        return
            isset($translations[$this->defaultLanguage]) ? $translations[$this->defaultLanguage] : reset($translations);
    }
}
