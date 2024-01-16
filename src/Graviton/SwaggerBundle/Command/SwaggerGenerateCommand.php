<?php
/**
 * Generates swagger.json
 */

namespace Graviton\SwaggerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates swagger.json
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SwaggerGenerateCommand extends Command
{

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
     * @var string $generatorHash hash
     */
    private $generatorHash;

    /**
     * constructor
     *
     * @param $generatorHash hash
     */
    public function __construct($generatorHash)
    {
        parent::__construct();
        $this->generatorHash = $generatorHash;
    }

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
     * @return int exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $swaggerFile = $this->rootDir . 'swagger.json';
        $swaggerHashFile = $this->rootDir . 'swagger.json.hash';

        $doGenerate = !($this->filesystem->exists($swaggerFile) && $this->filesystem->exists($swaggerHashFile));

        // really don't generate? -> compare hash
        if (!$doGenerate) {
            $currentHash = file_get_contents($swaggerHashFile);
            if ($this->generatorHash != $currentHash) {
                $doGenerate = true;
            }
        }

        if ($doGenerate) {
            $this->filesystem->dumpFile(
                $swaggerFile,
                json_encode($this->apidoc->getSwaggerSpec())
            );

            $this->filesystem->dumpFile(
                $swaggerHashFile,
                $this->generatorHash
            );

            echo 'wrote file '.$swaggerFile.PHP_EOL;
        } else {
            echo $swaggerFile.' already up to date, no need to generate...'.PHP_EOL;
        }

        return 0;
    }
}
