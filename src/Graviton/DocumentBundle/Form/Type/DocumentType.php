<?php
/**
 * generic form builder that grabs data from doctrine/serializer/json and builds forms
 */

namespace Graviton\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
     * @param array $fieldMap array to map document class names to fields
     */
    public function __construct(array $fieldMap)
    {
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
     * @param FormBuilderInterface $builder form builder
     * @param array                $options array of options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->fieldMap[$this->dataClass] as $field) {
            list($fieldName, $formName, $type, $options)  = $field;
            if ($fieldName !== $formName) {
                $options['property_path'] = $fieldName;
            }

            if ($type == 'form') {
                $type = clone $this;
                if (!isset($options['data_class'])) {
                    $options['data_class'] = 'stdclass';
                }
                $type->initialize($options['data_class']);
            } elseif ($type === 'date' || $type == 'datetime') {
                $options['widget'] = 'single_text';
                $options['input'] = 'string';
            } elseif ($type == 'collection' && $options['type'] == 'form') {
                $subType = clone $this;
                $subType->initialize($options['options']['data_class']);
                $options['type'] = $subType;
                $options['allow_add'] = true;
                $options['allow_delete'] = true;
            }
            $builder->add($formName, $type, $options);
        }
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
}
