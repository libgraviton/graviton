<?php
/**
 * create translation resources based on the available resources in the mongodb catalog
 */

namespace Graviton\I18nBundle\Command;

use Doctrine\MongoDB\Collection;
use Graviton\I18nBundle\Document\Translatable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Graviton\I18nBundle\Repository\TranslatableRepository;
use Graviton\I18nBundle\Document\Language;
use Symfony\Component\Translation\MessageCatalogue;
use Graviton\I18nBundle\Translator\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CreateTranslationResourcesCommand extends Command
{
    /**
     * @var LanguageRepository
     */
    private $languageRepo;

    /**
     * @var TranslatableRepository
     */
    private $translatableRepo;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $resourceDir;

    /**
     * @param LanguageRepository     $languageRepo     Language Repository
     * @param TranslatableRepository $translatableRepo Translatable Repository
     * @param Filesystem             $filesystem       symfony/filesystem tooling
     * @param TranslatorInterface    $translator       Resource translator
     */
    public function __construct(
        LanguageRepository $languageRepo,
        TranslatableRepository $translatableRepo,
        Filesystem $filesystem,
        TranslatorInterface $translator
    ) {
        $this->languageRepo = $languageRepo;
        $this->translatableRepo = $translatableRepo;
        $this->filesystem = $filesystem;
        $this->translator = $translator;
        $this->resourceDir = __DIR__.'/../Resources/translations/';

        parent::__construct();
    }

    /**
     * set up command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('graviton:i18n:create:resources')
            ->setDescription(
                'Create translation resource stub files for all the available languages/domains in the database'
            );
    }

    /**
     * run command
     *
     * @param InputInterface  $input  input interface
     * @param OutputInterface $output output interface
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating translation resource stubs");

        $languages = $this->languageRepo->findAll();
        $domains = $this->translatableRepo->createQueryBuilder()
            ->distinct('domain')
            ->select('domain')
            ->getQuery()
            ->execute()
            ->toArray();

        array_walk(
            $languages,
            function ($language) use ($output, $domains) {
                array_walk(
                    $domains,
                    function ($domain) use ($output, $language) {
                        /** @var Language $language */
                        $file = implode('.', [$domain, $language->getId(), 'odm']);
                        $this->filesystem->touch(implode(DIRECTORY_SEPARATOR, [$this->resourceDir, $file]));
                        $output->writeln("<info>Generated file $file</info>");

                        $locale = $language->getId();
                        $count = $this->generateResourceTranslations($domain, $locale);
                        $output->writeln("<info>Generated {$count} translations for {$domain}:{$locale}</info>");
                    }
                );
            }
        );
    }

    /**
     * Generate resource translations, return count of translations generated
     *
     * @param string $domain Translation domain name
     * @param string $locale Iso language locale string
     *
     * @return integer
     */
    private function generateResourceTranslations($domain, $locale)
    {
        /** @var Collection $translations */
        $translations = $this->translatableRepo->findBy(['domain' => $domain, 'locale' => $locale]);
        if (!$translations) {
            return 0;
        }

        /** @var MessageCatalogue $catalog */
        $catalog = $this->translator->getCatalogue($locale);

        $count = 0;
        /** @var Translatable $translation */
        foreach ($translations as $translation) {
            $catalog->set($translation->getOriginal(), $translation->getTranslated(), $domain);
            $count++;
        }

        return $count;
    }
}
