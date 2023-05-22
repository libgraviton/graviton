<?php
/**
 * shared stuff for generators
 */

namespace Graviton\GeneratorBundle\Generator;

use Graviton\GeneratorBundle\Twig\Extension;
use Graviton\GeneratorBundle\Twig\GeneratorExtension;
use Seld\JsonLint\JsonParser;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * shared stuff for generators
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class AbstractGenerator
{

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var array
     */
    private $exposeSyntheticMap = [];

    /**
     * AbstractGenerator constructor.
     */
    public function __construct()
    {
        $this->fs = new Filesystem();
        $this->twig = new Environment(
            new FilesystemLoader(__DIR__ . '/../Resources/skeleton'),
            [
                'debug' => true,
                'cache' => false,
                'strict_variables' => true,
                'autoescape' => false
            ]
        );
        $this->twig->addExtension(new GeneratorExtension());
    }

    /**
     * set ExposeSyntheticMap
     *
     * @param array $exposeSyntheticMap exposeSyntheticMap
     *
     * @return void
     */
    public function setExposeSyntheticMap($exposeSyntheticMap)
    {
        if (is_null($exposeSyntheticMap) && empty($exposeSyntheticMap)) {
            $exposeSyntheticMap = [];
        } elseif (is_string($exposeSyntheticMap)) {
            $exposeSyntheticMap = array_map('trim', explode(',', $exposeSyntheticMap));
        }

        $this->exposeSyntheticMap = $exposeSyntheticMap;

        $this->twig->addExtension(new Extension($exposeSyntheticMap));
    }

    /**
     * Check for the occurrence of "Bundle" in the given name and remove it
     *
     * @param String $name Bundle name
     *
     * @return string $name Bundle base name
     */
    public function getBundleBaseName($name)
    {
        if ('bundle' === strtolower(substr($name, -6))) {
            $name = substr($name, 0, -6);
        }

        return $name;
    }

    /**
     * {@inheritDoc}
     *
     * render a new object using twig
     *
     * @param string $template   template to use
     * @param array  $parameters info used in creating the object
     *
     * @return string
     */
    protected function render($template, $parameters)
    {
        return $this->twig->render($template, $parameters);
    }

    /**
     * renders a file form a twig template
     *
     * @param string $template   template filename
     * @param string $target     where to generate to
     * @param array  $parameters template params
     *
     * @return void
     */
    protected function renderFile($template, $target, $parameters)
    {
        $this->fs->dumpFile(
            $target,
            $this->render($template, $parameters)
        );
    }

    /**
     * renders a json file form a twig template
     *
     * @param string $template   template filename
     * @param string $target     where to generate to
     * @param array  $parameters template params
     *
     * @return void
     */
    protected function renderFileAsJson($template, $target, $parameters)
    {
        $json = $this->render($template, $parameters);
        $parser = new JsonParser();

        $lint = $parser->lint($json);
        if ($lint != null) {
            echo "ERROR IN FILE ".$target.PHP_EOL;
            throw $lint;
        }

        $content = json_decode($json);

        $this->fs->dumpFile(
            $target,
            json_encode($content, JSON_PRETTY_PRINT)
        );
    }
}
