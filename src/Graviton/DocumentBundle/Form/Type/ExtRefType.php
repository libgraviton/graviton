<?php
/**
 * form type for extref fields
 *
 * This automagically adds a transformer to all extref fields. This transformer is then responsible
 * for transforming any incoming URLs to MongoDBRefs that can be stored in MongoDB.
 */

namespace Graviton\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Graviton\DocumentBundle\Form\DataTransformer\ExtRefToMongoRefTransformer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefType extends AbstractType
{
    /**
     * construct
     *
     * @param RouterInterface $router   symfony router
     * @param array           $mapping  map of collection_name => route_id
     * @param array           $fields   map of fields to process
     * @param RequestStack    $requests request
     */
    public function __construct(RouterInterface $router, array $mapping, array $fields, RequestStack $requests)
    {
        $this->router = $router;
        $this->mapping = $mapping;
        $this->fields = $fields;
        $this->request = $requests->getCurrentRequest();
    }

    /**
     * @param FormBuilderInterface $builder form builder
     * @param array                $options configuration for builder
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ExtRefToMongoRefTransformer(
            $this->router,
            $this->mapping,
            $this->fields,
            $this->request
        );
        $builder->addViewTransformer($transformer);
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
