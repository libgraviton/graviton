<?php
/**
 * shared stuff for generators
 */

namespace Graviton\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

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
     * @private string[]
     */
    private $gravitonSkeletons;

    /**
     * Sets an array of directories to look for templates.
     *
     * The directories must be sorted from the most specific to the most generic
     * directory.
     *
     * @param array $gravitonSkeletons An array of skeleton dirs
     *
     * @return void
     */
    public function setSkeletonDirs($gravitonSkeletons)
    {
        $gravitonSkeletons = array_merge(
            array(__DIR__ . '/../Resources/skeleton'),
            $gravitonSkeletons
        );
        $this->gravitonSkeletons = is_array($gravitonSkeletons) ? $gravitonSkeletons : array($gravitonSkeletons);
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
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($this->gravitonSkeletons),
            array(
                'debug' => true,
                'cache' => false,
                'strict_variables' => true,
                'autoescape' => false,
            )
        );

        return $twig->render($template, $parameters);
    }
}
