<?php

namespace Graviton\I18nBundle\Listener;

use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * translate fields during serialization
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class I18nSerializationListener
{
    /**
     * @var mixed[]
     */
    protected $localizedFields = array();

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

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
     * set translator
     *
     * @param \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator translator
     *
     * @return void
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
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
                $this->getTranslatedField($value)
            );
        }
    }

    /**
     * build a complete translated field
     *
     * @param string $value value to translate
     *
     * @return array
     */
    private function getTranslatedField($value)
    {
        return array_map(
            function ($language) use ($value) {
                return $this->translator->trans($value, array(), 'messages', $language);
            },
            $this->request->attributes->get('languages')
        );
    }
}
