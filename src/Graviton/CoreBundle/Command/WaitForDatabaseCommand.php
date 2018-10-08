<?php
/**
 * wait for database availability and exit
 */
namespace Graviton\CoreBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Driver\Exception\ConnectionTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WaitForDatabaseCommand extends Command
{
    /**
     * @var DocumentManager
     */
    private $manager;
    /**
     * @param DocumentManager $manager manager
     */
    public function __construct(
        DocumentManager $manager
    ) {
        $this->manager = $manager;
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
            ->setName('graviton:core:wait-for-database')
            ->setDescription(
                'Blocks further execution until database is available'
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
        $output->writeln('Checking DB connection');
        $loopCount = 0;
        $connection = $this->manager->getConnection();

        $isConnected = false;
        while (!$isConnected) {
            try {
                $connection->connect();

                if (!$connection->isConnected()) {
                    throw new \Exception('Could not connect');
                }

                $isConnected = true;
            } catch (\Exception $e) {
                $output->writeln('DB is not yet connected, sleep 1 second.');
                sleep(1);
            } finally {
                $loopCount++;
            }

            if ($loopCount > 100) {
                throw new ConnectionTimeoutException('DB connection failed.');
            }
        }

        $output->writeln('DB connected.');
    }
}
