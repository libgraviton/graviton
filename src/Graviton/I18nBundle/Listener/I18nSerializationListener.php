<?php

namespace Graviton\I18nBundle\Listener;

use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Graviton\I18nBundle\Repository\LanguageRepository;

class I18nSerializationListener
{
    /**
     * @var mixed[]
     */
    protected $i18nFields = array();

    /**
     * @var Graviton\I18nBundle\Repository\LanguageRepository;
     */
    private $repository;

    /**
     * @var Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    protected $translator;

    /**
     * set language repository used for getting available languages
     *
     * @param Graviton\I18nBundle\Repository\LanguageRepository $repository repo
     *
     * @return void
     */
    public function setRepository(LanguageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * set translator
     *
     * @param Symfony\Bundle\FrameworkBundle\Translation\Translator $translator translator
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
        if (method_exists($object, 'setName')) {
            $this->i18nFields['name'] = $object->getName();
            $object->setName(null);
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
        if (method_exists($object, 'setName')) {
            foreach ($this->i18nFields AS $field => $value) {
                $event->getVisitor()->addData($field, array('en' => $value));
            }
        }
    }
}
