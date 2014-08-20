<?php

namespace Graviton\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\Common\Inflector\Inflector;

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
        $author = trim(`git config --get user.name`);
        $email = trim(`git config --get user.email`);

        $dir = $bundle->getPath();
        $basename = substr($document, 0, -6);
        $bundleNamespace = substr(get_class($bundle), 0, 0 - strlen($bundle->getName()));

        // add more info to the fields array
        $fields = array_map(
            function ($field) {

                // derive types for serializer from document types
                $field['serializerType'] = $field['type'];
                if (substr($field['type'], -2) == '[]') {
                    $field['serializerType'] = sprintf('array<%s>', substr($field['type'], 0, -2));
                }
                // @todo this assumtion is a hack and needs fixing
                if ($field['type'] === 'array') {
                    $field['serializerType'] = 'array<string>';
                }

                // add singular form
                $field['singularName'] = Inflector::singularize($field['fieldName']);

                return $field;
            },
            $fields
        );

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

        $this->generateDocument($parameters, $dir, $document, $withRepository);
        $this->generateSerializer($parameters, $dir, $document);
    }

    /**
     * generate document part of a resource
     *
     * @param array   $parameters     twig parameters
     * @param string  $dir            base bundle dir
     * @param string  $document       document name
     * @param boolean $withRepository generate repository class
     *
     * @return void
     */
    protected function generateDocument($parameters, $dir, $document, $withRepository)
    {
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

        if ($withRepository) {
            $this->renderFile(
                'document/DocumentRepository.php.twig',
                $dir.'/Repository/'.$document.'Repository.php',
                $parameters
            );
        }
    }

    /**
     * generate serializer part of a resource
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    protected function generateSerializer(array $parameters, $dir, $document)
    {
        $this->renderFile(
            'serializer/Document.xml.twig',
            $dir.'/Resources/config/serializer/Document.'.$document.'.xml',
            $parameters
        );
    }
}
