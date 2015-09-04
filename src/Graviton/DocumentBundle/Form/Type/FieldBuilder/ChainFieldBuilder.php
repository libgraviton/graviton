<?php
/**
 * ChainFieldBuilder class file
 */

namespace Graviton\DocumentBundle\Form\Type\FieldBuilder;

use Graviton\DocumentBundle\Form\Type\DocumentType;
use Symfony\Component\Form\FormInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ChainFieldBuilder implements FieldBuilderInterface
{
    /**
     * @var FieldBuilderInterface[]
     */
    private $builders = [];

    /**
     * Add field builder
     *
     * @param FieldBuilderInterface $builder Field builder
     * @return void
     */
    public function addFormFieldBuilder(FieldBuilderInterface $builder)
    {
        $this->builders[] = $builder;
    }

    /**
     * Is field type supported by this builder
     *
     * @param string $type    Field type
     * @param array  $options Options
     * @return bool
     */
    public function supportsField($type, array $options = [])
    {
        foreach ($this->builders as $builder) {
            if ($builder->supportsField($type, $options)) {
                return true;
            }
        }

        return false;
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
        foreach ($this->builders as $builder) {
            if ($builder->supportsField($type, $options)) {
                $builder->buildField(
                    $document,
                    $form,
                    $name,
                    $type,
                    $options,
                    $submittedData
                );
                return;
            }
        }
    }
}
