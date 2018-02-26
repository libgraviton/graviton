<?php
/**
 * translate fields during serialization
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\I18nBundle\Service\I18nUtils;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;

/**
 * translate fields during serialization
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @var DocumentManager
     */
    private $dm;

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
     * set doctrine odm documentmanager
     *
     * @param DocumentManager $dm dm
     *
     * @return void
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * remove translatable strings from object
     *
     * @param PreSerializeEvent $event event
     *
     * @return void
     * @throws \Exception
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();

        // Doctrine try to map value fields that may not exists.
        // @TODO why is this done here? clearify and maybe remove(?)
        // To know what object could not be serialised and what reference.
        try {
            $methods = get_class_methods($object);
            foreach ($methods as $method) {
                if (substr($method, 0, 3) == 'get') {
                    $object->$method();
                }
            }
        } catch (DocumentNotFoundException $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // Errors like missing identifier $id mapped by doctrine error, ignore.
            return;
        }

        if (!$object instanceof TranslatableDocumentInterface) {
            return;
        }

        $hash = \spl_object_hash($object);
        // If main object already build return.
        if (array_key_exists($hash, $this->localizedFields)) {
            return;
        }
        $this->localizedFields[$hash] = [];

        $translatable = $object->getTranslatableFields();
        if (!is_array($translatable)) {
            return;
        }

        // make sure any changes (like the set()s below) don't get persisted
        $this->dm->detach($object);

        foreach ($translatable as $field) {
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

        $hash = \spl_object_hash($object);
        if (!array_key_exists($hash, $this->localizedFields)) {
            return;
        }

        /** @var \JMS\Serializer\JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        foreach ($this->localizedFields[$hash] as $field => $value) {
            if (substr($field, -2, 2) === '[]') {
                $field = substr($field, 0, -2);
                $translated = array_map([$this->utils, 'getTranslatedField'], $value);
            } else {
                $translated = $this->utils->getTranslatedField($value);
            }

            if (!$visitor->hasData($field)) {
                $visitor->setData($field, $translated);
            }
        }
    }
}
