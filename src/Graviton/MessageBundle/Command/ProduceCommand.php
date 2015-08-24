<?php

namespace Graviton\MessageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ProduceCommand extends ContainerAwareCommand
{

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $producer = $this->getContainer()->get('old_sound_rabbit_mq.job_producer');
        $producer->setContentType('application/json');
        $producer->publish(
            $input->getArgument('data'),
            $input->getArgument('message')
        );
    }
}