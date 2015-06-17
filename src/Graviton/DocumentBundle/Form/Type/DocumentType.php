<?php
/**
 * generic form builder that grabs data from doctrine/serializer/json and builds forms
 */

namespace Graviton\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    private $classMap;

    /**
     * @var array
     */
    private $fieldMap;

    /**
     * @param array $classMap array for mappings from service id et al to classname
     * @param array $fieldMap array to map document class names to fields
     */
    public function __construct(array $classMap, array $fieldMap)
    {
        $this->classMap = $classMap;
        $this->fieldMap = $fieldMap;
    }

    /**
     * @param string $id identifier of service, maybe be a classname, serviceId
     *
     * @return void
     */
    public function initialize($id)
    {
        if (!array_key_exists($id, $this->classMap)) {
            throw new \RuntimeException(sprintf('Could not map service %s to class for form generator', $id));
        }
        $this->dataClass = $this->classMap[$id];
    }

    /**
     * @param FormBuilderInterface $builder form builder
     * @param array                $options array of options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $class = $this->dataClass;
        foreach ($this->fieldMap[$class] as $field) {
            list($name, $type, $options)  = $field;

            if ($type == 'form') {
                $type = clone $this;
                $type->initialize($options['data_class']);
            } elseif ($type == 'collection' && $options['type'] == 'form') {
                $subType = clone $this;
                $subType->initialize($options['options']['data_class']);
                $options['type'] = $subType;
                $options['allow_add'] = true;
                $options['allow_delete'] = true;
            }
            $builder->add($name, $type, $options);
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
     * @param OptionsResolverInterface $resolver resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->dataClass]);
    }
}
