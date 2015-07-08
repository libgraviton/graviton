<?php
/**
 * a dummy type based on text representing 'freeform' object ('hashes' in jsondef lingo)
 */

namespace Graviton\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FreeForm extends AbstractType
{
    /**
     * {@inheritdoc
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array());
    }

    /**
     * {@inheritdoc
     *
     * @return string parent name
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     *
     * @return string name
     */
    public function getName()
    {
        return 'freeform';
    }
}