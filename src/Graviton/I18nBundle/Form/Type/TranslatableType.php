<?php
/**
 * form type for translatable fields
 */

namespace Graviton\I18nBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Graviton\I18nBundle\Service\I18nUtils;
use Graviton\I18nBundle\Form\DataTransformer\TranslatableToDefaultStringTransformer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class TranslatableType extends AbstractType
{
    /**
     * @var I18nUtils
     */
    private $utils;

    /**
     * @var TranslatableToDefaultStringTransformer
     */
    private $transformer;

    /**
     * @param I18nUtils                              $utils       i18n utils for various needs
     * @param TranslatableToDefaultStringTransformer $transformer form transformer
     */
    public function __construct(I18nUtils $utils, TranslatableToDefaultStringTransformer $transformer)
    {
        $this->utils = $utils;
        $this->transformer = $transformer;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translatable';
    }

    /**
     * @param FormBuilderInterface $builder form builder
     * @param array                $options array of options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->transformer);

        $languages = $this->utils->getLanguages();
        $default = $this->utils->getDefaultLanguage();

        // handle what happens when no languages exist in db yet
        if (!in_array($default, $languages)) {
            $languages[] = $default;
        }

        foreach ($languages as $language) {
            $options = [];
            if ($language == $default) {
                $options['required'] = true;
            }
            $builder->add($language, 'text', $options);
        }
    }
}
