<?php

/**
 * Sends a given message to the message bus.
 */

namespace Graviton\RabbitMqBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Sends a given message to the message bus.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProduceCommand extends ContainerAwareCommand
{

    /**
     * Configures command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('graviton:message:produce')
            ->setDescription(
                'Puts a message onto the RabbitMQ channel.'
            )
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'The message to be sent e.g. "bundle.resource.update"'
            )
            ->addArgument(
                'data',
                InputArgument::OPTIONAL,
                'JSON formatted data to send to with the message.',
                '{}'
            );
    }

    /**
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $producer = $this->getContainer()->get('graviton.message.jobproducer');
        $producer->setContentType('application/json');
        $producer->publish(
            $input->getArgument('data'),
            $input->getArgument('message')
        );
    }
}
