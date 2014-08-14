<?php

namespace Graviton\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;

/**
 * generator command
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateResourceCommand extends GenerateDoctrineEntityCommand
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('graviton:generate:resource')
            ->setDescription('Generates a graviton rest resource');
    }

    /**
     * {@inheritDoc}
     *
     * @return ResourceGenerator
     */
    protected function createGenerator()
    {
        return new ResourceGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get('kernel')
        );
    }
}
