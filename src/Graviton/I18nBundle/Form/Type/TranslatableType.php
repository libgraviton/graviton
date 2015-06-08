<?php
/**
 * form type for translatable fields
 */

namespace Graviton\I18nBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Doctrine\ODM\MongoDB\DocumentRepository;

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
    private $languageRepository;

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
            $builder->add($language->getId(), 'text', []);
        }
    }
}
