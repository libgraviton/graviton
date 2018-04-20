<?php
/**
 * shared stuff for generators
 */

namespace Graviton\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * shared stuff for generators
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class AbstractGenerator extends Generator
{

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $fs;

    public function __construct()
    {
        $this->twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem(__DIR__ . '/../Resources/skeleton'),
            [
                'debug' => true,
                'cache' => false,
                'strict_variables' => true,
                'autoescape' => false
            ]
        );
        $this->fs = new Filesystem();
    }

    /**
     * Check for the occurence of "Bundle" in the given name and remove it
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

    protected function renderFile($template, $target, $parameters)
    {
        $this->fs->dumpFile(
            $target,
            $this->render($template, $parameters)
        );
    }
}
