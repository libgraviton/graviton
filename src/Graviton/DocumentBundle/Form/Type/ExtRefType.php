<?php
/**
 * form type for extref fields
 *
 * This automagically adds a transformer to all extref fields. This transformer is then responsible
 * for transforming any incoming URLs to MongoDBRefs that can be stored in MongoDB.
 */

namespace Graviton\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefType extends AbstractType
{
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
