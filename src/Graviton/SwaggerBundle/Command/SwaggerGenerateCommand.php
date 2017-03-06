<?php
/**
 * Generates swagger.json
 */

namespace Graviton\SwaggerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generates swagger.json
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SwaggerGenerateCommand extends Command
{

    /**
     * container
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * root dir
     *
     * @var string
     */
    private $rootDir;

    /**
     * filesystem
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * apidoc service
     *
     * @var \Graviton\SwaggerBundle\Service\Swagger
     */
    private $apidoc;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:swagger:generate')
             ->setDescription(
                 'Generates swagger.json in web dir'
             );
    }

    /**
     * set container
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * sets the root dir
     *
     * @param string $rootDir root dir
     *
     * @return void
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * set filesystem
     *
     * @param mixed $filesystem filesystem
     *
     * @return void
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * sets apidoc
     *
     * @param mixed $apidoc apidoc
     *
     * @return void
     */
    public function setApidoc($apidoc)
    {
        $this->apidoc = $apidoc;
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem->dumpFile(
            $this->rootDir.'/../app/cache/swagger.json',
            json_encode($this->apidoc->getSwaggerSpec())
        );
    }
}
