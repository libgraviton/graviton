<?php
/**
 * AbstractValidateCommand class file
 */

namespace Graviton\JsonSchemaBundle\Command;

use Graviton\JsonSchemaBundle\Exception\ValidationExceptionError;
use Graviton\JsonSchemaBundle\Validator\ValidatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Abstract validate JSON definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class AbstractValidateCommand extends Command
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Load JSON definitions
     *
     * @param InputInterface $input Command input
     * @return SplFileInfo[]
     */
    abstract protected function loadDefinitions(InputInterface $input);

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
     * Executes command
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hasErrors = array_reduce(
            $this->loadDefinitions($input),
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
     * @return ValidationExceptionError[]
     */
    protected function validateJsonDefinitionFile($json)
    {
        try {
            return $this->validator->validateJsonDefinition($json);
        } catch (\Exception $e) {
            return [new ValidationExceptionError(['message' => $e->getMessage()])];
        }
    }

    /**
     * Convert errors to table rows
     *
     * @param OutputInterface            $output   Output
     * @param SplFileInfo                $fileInfo File info
     * @param ValidationExceptionError[] $errors   Errors
     * @return void
     */
    protected function outputErrors(OutputInterface $output, SplFileInfo $fileInfo, array $errors)
    {
        $rows = [];
        foreach ($errors as $error) {
            $rows[] = new TableSeparator();
            $rows[] = [
                $error->getProperty(),
                wordwrap($error->getMessage(), 80, PHP_EOL, false),
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
}
