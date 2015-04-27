<?php
/**
 * translate fields during serialization
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18nBundle\Service\I18nUtils;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * translate fields during serialization
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class I18nSerializationListener
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
     * @param PreSerializeEvent $event event
     *
     * @return void
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();

        $this->localizedFields[\spl_object_hash($object)] = array();

        try {
            if ($object instanceof TranslatableDocumentInterface) {
                foreach ($object->getTranslatableFields() as $field) {
                    $setter = 'set'.ucfirst($field);
                    $getter = 'get'.ucfirst($field);

                    // only allow objects that we can update during postSerialize
                    if (method_exists($object, $setter)) {
                            $this->localizedFields[\spl_object_hash($object)][$field] = $object->$getter();
                            // remove untranslated field to make space for translation struct
                            $object->$setter(null);
                    }
                }
            }
        } catch (\Doctrine\ODM\MongoDB\DocumentNotFoundException $e) {
            // @todo if a document references a non-existing document - handle it so it renders to null!
        }
    }

    /**
     * translate all strings marked as multi lang
     *
     * @param ObjectEvent $event serialization event
     *
     * @return void
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();
        foreach ($this->localizedFields[\spl_object_hash($object)] as $field => $value) {
            $event->getVisitor()->addData(
                $field,
                $this->utils->getTranslatedField($value)
            );
        }
    }
}
