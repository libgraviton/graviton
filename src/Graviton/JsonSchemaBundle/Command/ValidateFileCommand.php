<?php
/**
 * ValidateFileCommand class file
 */

namespace Graviton\JsonSchemaBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Validate JSON definition file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValidateFileCommand extends AbstractValidateCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:validate:definition:file');

        $this
            ->setDescription('Validate a JSON definition')
            ->addOption(
                'path',
                '',
                InputOption::VALUE_REQUIRED,
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
        $file = $input->getOption('path');
        return [new SplFileInfo($file, $file, $file)];
    }
}
