<?php
/**
 *
 */

namespace Graviton\DocumentBundle\Form\Type;

use Graviton\DocumentBundle\Form\DataTransformer\BooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StrictBooleanType extends AbstractType
{
    /**
     * @var BooleanTransformer
     */
    private $transformer;

    /**
     * @inheritDoc
     */
    function __construct(BooleanTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("empty_data", null);
        parent::configureOptions($resolver);
    }


    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->transformer->setPropertyPath($builder->getPropertyPath());
        $builder->resetViewTransformers();
        $builder->addViewTransformer($this->transformer);
        parent::buildForm($builder, $options);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return 'checkbox';
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'strictboolean';
    }

}