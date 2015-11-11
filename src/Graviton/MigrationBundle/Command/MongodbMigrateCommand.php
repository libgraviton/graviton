<?php
/**
 * graviton:mongodb:migrations:execute command
 */

namespace Graviton\MigrationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class MongodbMigrateCommand extends Command
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @param Finder $finder finder that finds configs
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;

        parent::__construct();
    }

    /**
     * setup command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:mongodb:migrate');
    }

    /**
     * call execute on found commands
     *
     * @param InputInterface  $input  user input
     * @param OutputInterface $output command output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->finder->in(
            strpos(getcwd(), 'vendor/') === false ? getcwd() :  getcwd() . '/../../../../'
        )->path('Resources/config')->name('/migrations.(xml|yml)/')->files();

        foreach ($this->finder as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $output->writeln('Found '.$file->getRelativePathname());

            $command = $this->getApplication()->find('mongodb:migrations:migrate');

            $arguments = $input->getArguments();
            $arguments['command'] = 'mongodb:migrations:migrate';
            $arguments['--configuration'] = $file->getRelativePathname();

            $migrateInput = new ArrayInput($arguments);
            $returnCode = $command->run($migrateInput, $output);

            if ($returnCode !== 0) {
                $output->writeln(
                    '<error>Calling mongodb:migrations:migrate failed for '.$file->getRelativePathname().'</error>'
                );
                return $returnCode;
            }
        }
    }
}
