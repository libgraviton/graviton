<?php

namespace Graviton\I18nBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Graviton\I18nBundle\Document\Translatable;

/**
 * generate missing translation entities based on english strings
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class LoadMissingCommand extends ContainerAwareCommand
{
    /**
     * base strings to translate (en)
     *
     * @var Translatable[]
     */
    protected $baseStrings;

    /**
     * set up command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('graviton:i118n:load:missing')
            ->setDescription('Generate translatables for strings in en.');
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
        $this->generateTranslatables();
    }

    /**
     * loop over languages
     *
     * @return void
     */
    private function generateTranslatables()
    {
        $container = $this->getContainer();
        $languages = $container->get('graviton.i18n.repository.language')->findAll();
        $this->baseStrings = $container->get('graviton.i18n.repository.translatable')->findBy(array('locale' => 'en'));

        array_walk(
            $languages,
            function ($language) {
                $this->generateForLanguage($language->getId());
            }
        );
    }

    /**
     * generate strings for languages
     *
     * @param Language $language language
     *
     * @return void
     */
    private function generateForLanguage($language)
    {
        if ($language == 'en') {
            return;
        }
        array_walk(
            $this->baseStrings,
            function ($base) use ($language) {
                $domain = $base->getDomain();
                $original = $base->getOriginal();
                $id = implode('-', array($domain, $language, $original));

                $record = new Translatable;
                $record->setId($id);
                $record->setDomain($domain);
                $record->setLocale($language);
                $record->setOriginal($original);

                $this->getContainer()->get('graviton.i18n.model.translatable')->insertRecord($record);
            }
        );
    }
}
