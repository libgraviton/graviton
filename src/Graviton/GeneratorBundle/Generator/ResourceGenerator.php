<?php

namespace Graviton\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * bundle containing various code generators
 *
 * This code is more or less loosley based on SensioBundleGenerator. It could
 * use some refactoring to duplicate less for that, but this is how i finally
 * got a working version.
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ResourceGenerator extends Generator
{
    /**
     * @private string[]
     */
    private $skeletonDirs;
    /**
     * @private
     */
    private $filesystem;
    /**
     * @private
     */
    private $doctrine;
    /**
     * @private
     */
    private $kernel;

    /**
     * instanciate generator object
     *
     * @param object $filesystem fs abstraction layer
     * @param object $doctrine   dbal
     * @param object $kernel     app kernel
     *
     * @return ResourceGenerator
     */
    public function __construct($filesystem, $doctrine, $kernel)
    {
        $this->filesystem = $filesystem;
        $this->doctrine = $doctrine;
        $this->kernel = $kernel;
    }

    /**
     * Sets an array of directories to look for templates.
     *
     * The directories must be sorted from the most specific to the most
     * directory.
     *
     * @param array $skeletonDirs An array of skeleton dirs
     *
     * @return void
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        $skeletonDirs = array_merge(
            array(__DIR__.'/../Resources/SensioGeneratorBundle/skeleton'),
            $skeletonDirs
        );
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);
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
            new \Twig_Loader_Filesystem($this->skeletonDirs),
            array(
                'debug'            => true,
                'cache'            => false,
                'strict_variables' => true,
                'autoescape'       => false,
            )
        );

        return $twig->render($template, $parameters);
    }

    /**
     * generate the resource with all its bits and parts
     *
     * @param BundleInterface $bundle         bundle
     * @param string          $document       document name
     * @param string          $format         format of config files (please use xml)
     * @param array           $fields         fields to add
     * @param boolean         $withRepository generate repository class
     *
     * @return void
     */
    public function generate(BundleInterface $bundle, $document, $format, array $fields, $withRepository)
    {
        $dir = $bundle->getPath();
        $author = trim(`git config --get user.name`);
        $email = trim(`git config --get user.email`);

        $basename = substr($document, 0, -6);
        $bundleNamespace = substr(get_class($bundle), 0, 0 - strlen($bundle->getName()));
        $parameters = array(
            'document'  => $document,
            'base'      => $bundleNamespace,
            'bundle'    => $bundle->getName(),
            'format'    => $format,
            'author'    => $author,
            'email'     => $email,
            'fields'    => $fields,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );

        $this->renderFile(
            'document/Document.mongodb.xml.twig',
            $dir.'/Resources/config/doctrine/'.$document.'.mongodb.xml',
            $parameters
        );

        $this->renderFile(
            'document/Document.php.twig',
            $dir.'/Document/'.$document.'.php',
            $parameters
        );
        return;

        // run built in doctrine commands
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
        $application->setAutoExit(false);
        $options = array('command' => 'doctrine:mongodb:generate:documents', 'bundle' => $bundle->getName());
        $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
        return;
        $this->renderFile(
            'bundle/Extension.php.twig',
            $dir.'/DependencyInjection/'.$basename.'Extension.php',
            $parameters
        );

        if ('xml' === $format || 'annotation' === $format) {
            $this->renderFile('bundle/services.xml.twig', $dir.'/Resources/config/services.xml', $parameters);
            $this->renderFile('bundle/config.xml.twig', $dir.'/Resources/config/config.xml', $parameters);
        } else {
            $this->renderFile(
                'bundle/services.'.$format.'.twig',
                $dir.'/Resources/config/services.'.$format,
                $parameters
            );
            $this->renderFile(
                'bundle/config.'.$format.'.twig',
                $dir.'/Resources/config/config.'.$format,
                $parameters
            );
        }

        if ('annotation' != $format) {
            $this->renderFile(
                'bundle/routing.'.$format.'.twig',
                $dir.'/Resources/config/routing.'.$format,
                $parameters
            );
        }

        if ($structure) {
            $this->filesystem->mkdir($dir.'/Resources/doc');
            $this->filesystem->touch($dir.'/Resources/doc/index.rst');
        }
    }
}
