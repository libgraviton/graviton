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
     * @var I18nUtils
     */
    protected $utils;

    /**
     * set utils (i18nutils)
     *
     * @param I18nUtils $utils utils
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
        if (!$object instanceof TranslatableDocumentInterface) {
            return;
        }

        try {
            $hash = \spl_object_hash($object);
            $this->localizedFields[$hash] = [];
            foreach ($object->getTranslatableFields() as $field) {
                $isArray = substr($field, -2, 2) === '[]';
                $method = $isArray ? substr($field, 0, -2) : $field;

                $setter = 'set'.ucfirst($method);
                $getter = 'get'.ucfirst($method);
                if (!method_exists($object, $setter) || !method_exists($object, $getter)) {
                    continue;
                }

                // only allow objects that we can update during postSerialize
                $value = $object->$getter();
                if (($isArray && !empty($value)) || (!$isArray && $value != null)) {
                    $this->localizedFields[$hash][$field] = $value;
                    // remove untranslated field to make space for translation struct
                    $object->$setter(null);
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
        if (!$object instanceof TranslatableDocumentInterface) {
            return;
        }

        foreach ($this->localizedFields[\spl_object_hash($object)] as $field => $value) {
            if (substr($field, -2, 2) === '[]') {
                $field = substr($field, 0, -2);
                $event->getVisitor()->addData(
                    $field,
                    array_map([$this->utils, 'getTranslatedField'], $value)
                );
            } else {
                $event->getVisitor()->addData(
                    $field,
                    $this->utils->getTranslatedField($value)
                );
            }
        }
    }
}
