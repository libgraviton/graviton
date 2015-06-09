<?php
/**
 * form type for translatable fields
 */

namespace Graviton\I18nBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Graviton\I18nBundle\Repository\LanguageRepository;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class TranslatableType extends AbstractType
{
    /**
     * @var LanguageRepository
     */
    private $languageRepo;

    /**
     * @param LanguageRepository $languageRepo repo of available languages
     */
    public function __construct(LanguageRepository $languageRepo)
    {
        $this->languageRepo = $languageRepo;
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
        foreach ($this->languageRepo->findAll() as $language) {
            $options = [];
            $id = $language->getId();
            if ($id == 'en') {
                $options['required'] = true;
            }
            $builder->add($id, 'text', $options);
        }
    }
}
