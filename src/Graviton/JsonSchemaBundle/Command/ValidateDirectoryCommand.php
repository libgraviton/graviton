<?php
/**
 * ValidateDirectoryCommand class file
 */

namespace Graviton\JsonSchemaBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Validate JSON definition in directory
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValidateDirectoryCommand extends AbstractValidateCommand
{
    /**
     * @var string
     */
    private $defaultPath;

    /**
     * Set default scan path
     *
     * @param string $path Path
     * @return void
     */
    public function setDefaultPath($path)
    {
        $this->defaultPath = $path;
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:validate:definition:directory');

        $this
            ->setDescription('Validate a JSON definition')
            ->addOption(
                'path',
                '',
                InputOption::VALUE_OPTIONAL,
                'Path to the json definition.'
            );
    }

    /**
     * Load JSON definitions
     *
     * @param InputInterface $input Command input
     * @return SplFileInfo[]
     */
    protected function loadDefinitions(InputInterface $input)
    {
        $path = $input->getOption('path');
        if ($path === null) {
            $path = $this->defaultPath;
        }

        // if we are vendorized we will search all vendor paths
        if (strpos($path, 'vendor/graviton/graviton')) {
            $path .= '/../../';
        }

        $finder = (new Finder())
            ->files()
            ->in($path)
            ->name('*.json')
            ->notName('_*')
            ->path('/(^|\/)resources\/definition($|\/)/i')
            ->notPath('/(^|\/)Tests($|\/)/i');
        return iterator_to_array($finder);
    }
}
