<?php
/**
 * form type for extref fields
 *
 * This automagically adds a transformer to all extref fields. This transformer is then responsible
 * for transforming any incoming URLs to MongoDBRefs that can be stored in MongoDB.
 */

namespace Graviton\DocumentBundle\Form\Type;

use Graviton\DocumentBundle\Form\DataTransformer\ExtRefTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefType extends AbstractType
{
    /**
     * @var ExtRefTransformer
     */
    private $extRefTransformer;

    /**
     * Constructor
     *
     * @param ExtRefTransformer $extRefTransformer Ext reference data transformer
     */
    public function __construct(ExtRefTransformer $extRefTransformer)
    {
        $this->extRefTransformer = $extRefTransformer;
    }

    /**
     * @param OptionsResolver $resolver option resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['invalid_message' => 'The referenced URL is invalid.']);
    }

    /**
     * Builds the form
     *
     * @param FormBuilderInterface $builder Builder
     * @param array                $options Options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->extRefTransformer);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'url';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'extref';
    }
}
