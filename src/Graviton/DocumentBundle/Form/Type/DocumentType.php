<?php
/**
 * generic form builder that grabs data from doctrine/serializer/json and builds forms
 */

namespace Graviton\DocumentBundle\Form\Type;

use Graviton\DocumentBundle\Form\Type\FieldBuilder\FieldBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentType extends AbstractType
{
    /**
     * @var string
     */
    private $dataClass;
    /**
     * @var array
     */
    private $fieldMap;
    /**
     * @var FieldBuilderInterface
     */
    private $fieldBuilder;

    /**
     * Constructor
     *
     * @param FieldBuilderInterface $fieldBuilder Field builder
     * @param array                 $fieldMap     array to map document class names to fields
     */
    public function __construct(FieldBuilderInterface $fieldBuilder, array $fieldMap)
    {
        $this->fieldBuilder = $fieldBuilder;
        $this->fieldMap = $fieldMap;
    }

    /**
     * @param string $documentClass Document class
     * @return void
     */
    public function initialize($documentClass)
    {
        if (!isset($this->fieldMap[$documentClass])) {
            throw new \RuntimeException(sprintf('Could not find form config for document %s', $documentClass));
        }

        $this->dataClass = $documentClass;
    }

    /**
     * Get child form with specified class
     *
     * @param string $documentClass Document class
     * @return DocumentType
     */
    public function getChildForm($documentClass)
    {
        $clone = clone $this;
        $clone->initialize($documentClass);

        return $clone;
    }

    /**
     * @param FormBuilderInterface $builder form builder
     * @param array                $options array of options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // We have to build a document form in PRE_SUBMIT event handler
        // because we need to configure required flag depending on submitted data.
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'handlePreSubmitEvent']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return strtolower(strtr($this->dataClass, '\\', '_'));
    }

    /**
     * @param OptionsResolver $resolver resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->dataClass]);
    }

    /**
     * Handle "presubmit" event
     *
     * @param FormEvent $event Submit event
     * @return void
     */
    public function handlePreSubmitEvent(FormEvent $event)
    {
        if (empty($this->fieldMap[$this->dataClass])) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();
        if (!$form->isRequired() && $data === null) {
            return;
        }

        foreach ($this->fieldMap[$this->dataClass] as $config) {
            list($name, $type, $options) = $config;
            if (!$this->fieldBuilder->supportsField($type, $options)) {
                throw new \LogicException(sprintf('Could not build field "%s" with options "%s"', $type, json_encode($options)));
            }

            $this->fieldBuilder->buildField(
                $this,
                $form,
                $name,
                $type,
                $options,
                is_array($data) && isset($data[$name]) ? $data[$name] : null
            );
        }
    }
}
