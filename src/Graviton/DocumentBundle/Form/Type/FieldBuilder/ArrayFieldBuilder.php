<?php
/**
 * ArrayFieldBuilder class file
 */

namespace Graviton\DocumentBundle\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Symfony\Component\Form\FormInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ArrayFieldBuilder implements FieldBuilderInterface
{
    /**
     * Is field type supported by this builder
     *
     * @param string $type    Field type
     * @param array  $options Options
     * @return bool
     */
    public function supportsField($type, array $options = [])
    {
        return $type === 'collection' && isset($options['type']) && $options['type'] !== 'form';
    }

    /**
     * Build form field
     *
     * @param DocumentType  $document      Document type
     * @param FormInterface $form          Form
     * @param string        $name          Field name
     * @param string        $type          Field type
     * @param array         $options       Options
     * @param mixed         $submittedData Submitted data
     * @return void
     */
    public function buildField(
        DocumentType $document,
        FormInterface $form,
        $name,
        $type,
        array $options = [],
        $submittedData = null
    ) {
        $options['allow_add'] = true;
        $options['allow_delete'] = true;
        $options['prototype'] = false;

        if ('strictboolean' === $options['type']) {
            $options['options']['submitted_data'] = $submittedData;
        }

        $form->add($name, $type, $options);
    }
}
