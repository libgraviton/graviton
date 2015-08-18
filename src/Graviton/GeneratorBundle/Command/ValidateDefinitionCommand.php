<?php
/**
 * ValidateDefinitionCommand class file
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Definition\Validator\ValidatorInterface;
use HadesArchitect\JsonSchemaBundle\Error\Error;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Validate JSON definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidateDefinitionCommand extends Command
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var string
     */
    private $defaultPath;

    /**
     * Set validator
     *
     * @param ValidatorInterface $validator Definition validator
     * @return void
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

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
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('graviton:validate:definition')
            ->setDescription('Validate a JSON definition')
            ->addOption(
                'path',
                '',
                InputOption::VALUE_OPTIONAL,
                'Path to the json definition.'
            );
    }

    /**
     * Executes command
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('path');
        if ($path === null) {
            $path = $this->defaultPath;
        }

        $hasErrors = array_reduce(
            iterator_to_array($this->loadDefinitions($path)),
            function ($hasErrors, SplFileInfo $fileInfo) use ($output) {
                $errors = $this->validateJsonDefinitionFile($fileInfo->getContents());
                if (!empty($errors)) {
                    $hasErrors = true;
                    $this->outputErrors($output, $fileInfo, $errors);
                }

                return $hasErrors;
            },
            false
        );
        return $hasErrors ? 1 : 0;
    }

    /**
     * Validate JSON definition
     *
     * @param string $json JSON definition
     * @return Error[]
     */
    private function validateJsonDefinitionFile($json)
    {
        try {
            return $this->validator->validateJsonDefinition($json);
        } catch (\Exception $e) {
            return new Error('', $e->getMessage());
        }
    }

    /**
     * Convert errors to table rows
     *
     * @param OutputInterface $output   Output
     * @param SplFileInfo     $fileInfo File info
     * @param Error[]         $errors   Errors
     * @return void
     */
    private function outputErrors(OutputInterface $output, SplFileInfo $fileInfo, array $errors)
    {
        $rows = [];
        foreach ($errors as $error) {
            $rows[] = new TableSeparator();
            $rows[] = [
                $error->getProperty(),
                wordwrap($error->getViolation(), 80, PHP_EOL, false),
            ];
        }
        array_shift($rows);

        $output->writeln('<comment>'.$fileInfo->getRelativePathname().'</comment>');
        (new Table($output))
            ->setHeaders(['Path', 'Error'])
            ->setRows($rows)
            ->render();
        $output->writeln('');
    }

    /**
     * Load raw JSON definitions
     *
     * @param string $path Path to scan
     * @return Finder
     */
    private function loadDefinitions($path)
    {
        // if we are vendorized we will search all vendor paths
        if (strpos($path, 'vendor/graviton/graviton')) {
            $path .= '/../../';
        }

        return (new Finder())
            ->files()
            ->in($path)
            ->name('*.json')
            ->notName('_*')
            ->path('/(^|\/)resources\/definition($|\/)/i')
            ->notPath('/(^|\/)Tests($|\/)/i');
    }
}
