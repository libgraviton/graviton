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
     * @param array $classMap array for mappings from service id et al to classname
     */
    public function __construct(array $classMap)
    {
        $this->classMap = $classMap;
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
        $builder->add('title', 'translatable', []);
        $builder->add('showInMenu', 'radio', []);
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
