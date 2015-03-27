<?php
/**
 * translate fields during serialization
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18NBundle\Service\I18NUtils;
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
    protected $intUtils;

    /**
     * set intUtils (i18nutils)
     *
     * @param \Graviton\I18NBundle\Service\I18NUtils $intUtils utils
     *
     * @return void
     */
    public function setIntUtils(I18NUtils $intUtils)
    {
        $this->intUtils = $intUtils;
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
        $defaultLanguage = $this->intUtils->getDefaultLanguage();
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
                $this->intUtils->insertTranslatable($values);
            }
        );
    }
}
