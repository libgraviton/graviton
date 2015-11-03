<?php
/**
 * strict boolean type
 */

namespace Graviton\DocumentBundle\Form\Type;

use Graviton\DocumentBundle\Form\DataTransformer\BooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * strict boolean type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class StrictBooleanType extends AbstractType
{
    /**
     * @var BooleanTransformer
     */
    private $transformer;

    /**
     * strict boolean type
     *
     * @param BooleanTransformer $transformer boolean transformer
     */
    public function __construct(BooleanTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     *
     * @param OptionsResolver $resolver The resolver for the options
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('submitted_data');
        $resolver->setDefault('submitted_data', null);
    }


    /**
     * @inheritDoc
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataTransformer = clone $this->transformer;
        $propertyPath = $builder->getPropertyPath();
        if ($propertyPath !== null) {
            $dataTransformer->setPropertyPath($propertyPath);
        }
        $dataTransformer->setSubmittedData($options['submitted_data']);

        // we won't use the standard view transformer, which is defined by the checkbox type
        $builder->resetViewTransformers();
        $builder->addViewTransformer($dataTransformer);
    }

    /**
     * @inheritDoc
     *
     * @return string The name of the parent type
     */
    public function getParent()
    {
        return 'checkbox';
    }


    /**
     * @inheritDoc
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'strictboolean';
    }
}
