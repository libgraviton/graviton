<?php
/**
 * FormFieldBuilder class file
 */

namespace Graviton\DocumentBundle\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Symfony\Component\Form\FormInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FormFieldBuilder implements FieldBuilderInterface
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
        return $type === 'form';
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
        if (!isset($options['data_class'])) {
            $options['data_class'] = 'stdclass';
        }

        // we set "required" flag to "true" if submitted data is not null
        // because required field cannot be a child of the optional field
        if (!isset($options['required']) || !$options['required']) {
            $options['required'] = $submittedData !== null;
        }

        $form->add(
            $name,
            $document->getChildForm($options['data_class']),
            $options
        );
    }
}
