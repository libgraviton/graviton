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
        $this->generateModel($parameters, $dir, $document);
        $this->generateController($parameters, $dir, $document);
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

        $services = $this->loadServices($dir);

        $bundleParts = explode('\\', $parameters['base']);
        $shortName = strtolower($bundleParts[0]);
        $shortBundle = strtolower(substr($bundleParts[1], 0, -6));

        $docName = implode(
            '.',
            array(
                $shortName,
                $shortBundle,
                'document',
                strtolower($parameters['document'])
            )
        );

        $services = $this->addParam(
            $services,
            $docName.'.class',
            $parameters['base'].'Document\\'.$parameters['document']
        );

        $services = $this->addService(
            $services,
            $docName
        );

        if ($withRepository) {
            $repoName = implode(
                '.',
                array(
                    $shortName,
                    $shortBundle,
                    'repository',
                    strtolower($parameters['document'])
                )
            );

            $services = $this->addParam(
                $services,
                $repoName.'.class',
                $parameters['base'].'Repository\\'.$parameters['document']
            );

            $services = $this->addService(
                $services,
                $repoName,
                null,
                null,
                array(),
                null,
                array(
                    array(
                        'type' => 'string',
                        'value' => $parameters['bundle'].':'.$document
                    )
                ),
                'doctrine_mongodb.odm.default_document_manager',
                'getRepository'
            );
 
            $this->renderFile(
                'document/DocumentRepository.php.twig',
                $dir.'/Repository/'.$document.'Repository.php',
                $parameters
            );
        }

        file_put_contents($dir.'/Resources/config/services.xml', $services->saveXML());
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

    /**
     * generate model poart of a resource
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    protected function generateModel(array $parameters, $dir, $document)
    {
        $this->renderFile(
            'model/Model.php.twig',
            $dir.'/Model/'.$document.'.php',
            $parameters
        );

        $services = $this->loadServices($dir);

        $bundleParts = explode('\\', $parameters['base']);
        $shortName = strtolower($bundleParts[0]);
        $shortBundle = strtolower(substr($bundleParts[1], 0, -6));
        $paramName = implode('.', array($shortName, $shortBundle, 'model', strtolower($parameters['document'])));
        $repoName = implode('.', array($shortName, $shortBundle, 'repository', strtolower($parameters['document'])));

        $services = $this->addParam(
            $services,
            $paramName.'.class',
            $parameters['base'].'Model\\'.$parameters['document']
        );

        $services = $this->addService(
            $services,
            $paramName,
            'graviton.rest.model',
            null,
            array(
                array(
                    'method' => 'setRepository',
                    'service' => $repoName
                )
            )
        );
 
        file_put_contents($dir.'/Resources/config/services.xml', $services->saveXML());
    }

    /**
     * generate RESTful controllers ans service configs
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    protected function generateController(array $parameters, $dir, $document)
    {
        $this->renderFile(
            'controller/DocumentController.php.twig',
            $dir.'/Controller/'.$document.'Controller.php',
            $parameters
        );
        
        $services = $this->loadServices($dir);

        $bundleParts = explode('\\', $parameters['base']);
        $shortName = strtolower($bundleParts[0]);
        $shortBundle = strtolower(substr($bundleParts[1], 0, -6));
        $paramName = implode('.', array($shortName, $shortBundle, 'controller', strtolower($parameters['document'])));

        $services = $this->addParam(
            $services,
            $paramName.'.class',
            $parameters['base'].'Controller\\'.$parameters['document']
        );

        $services = $this->addService(
            $services,
            $paramName,
            'graviton.rest.controller',
            'request',
            array(
                array(
                  'method' => 'setModel',
                  'service' => implode(
                      '.',
                      array($shortName, $shortBundle, 'model', strtolower($parameters['document']))
                  )
                )
            ),
            'graviton.rest'
        );

        file_put_contents($dir.'/Resources/config/services.xml', $services->saveXML());
    }

    /**
     * load services.xml
     *
     * @param string $dir base dir
     *
     * @return \DOMDocument
     */
    private function loadServices($dir)
    {
        $services = new \DOMDocument;
        $services->formatOutput = true;
        $services->preserveWhiteSpace = false;
        $services->load($dir.'/Resources/config/services.xml');

        return $services;
    }

    /**
     * add param to services.xml
     *
     * @param \DOMDocument $dom   services.xml document
     * @param string       $key   parameter key
     * @param string       $value parameter value
     *
     * @return \DOMDocument
     */
    private function addParam(\DOMDocument $dom, $key, $value)
    {
        $container = $dom->getElementsByTagName('container')->item(0);

        // add <parameters> if missing
        $paramNodes = $container->getElementsByTagName('parameters');
        if ($paramNodes->length < 1) {
            $paramNode = $dom->createElement('parameters');
            $container->appendChild($paramNode);
        } else {
            $paramNode = $paramNodes->item(0);
        }

        $xpath = new \DomXpath($dom);

        $nodes = $xpath->query('//parameters/parameter[@key="'.$key.'.class"]');
        if ($nodes->length < 1) {
            $attrNode = $dom->createElement('parameter', $value);

            $attrKey = $dom->createAttribute('key');
            $attrKey->value = $key;

            $attrNode->appendChild($attrKey);
            $paramNode->appendChild($attrNode);
        }

        return $dom;
    }

    /**
     * add service to services.xml
     *
     * @param \DOMDocument $dom            services.xml dom
     * @param string       $id             id of new service
     * @param string       $parent         parent for service
     * @param string       $scope          scope of service
     * @param array        $calls          methodCalls to add
     * @param string       $tag            tag name or empty if no tag needed
     * @param array        $arguments      service arguments
     * @param string       $factoryService factory service id
     * @param string       $factoryMethod  factory method name
     *
     * @return \DOMDocument
     */
    private function addService(
        $dom,
        $id,
        $parent = null,
        $scope = null,
        array $calls = array(),
        $tag = null,
        array $arguments = array(),
        $factoryService = null,
        $factoryMethod = null
    ) {
        $container = $dom->getElementsByTagName('container')->item(0);

        // add <services> if missing
        $servicesNodes = $container->getElementsByTagName('services');
        if ($servicesNodes->length < 1) {
            $servicesNode = $dom->createElement('services');
            $container->appendChild($servicesNode);
        } else {
            $servicesNode = $servicesNodes->item(0);
        }

        $xpath = new \DomXpath($dom);

        // add controller to services
        $nodes = $xpath->query('//services/service[@id="'.$id.'"]');
        if ($nodes->length < 1) {
            $attrNode = $dom->createElement('service');

            $attrKey = $dom->createAttribute('id');
            $attrKey->value = $id;
            $attrNode->appendChild($attrKey);

            $attrKey = $dom->createAttribute('class');
            $attrKey->value = '%'.$id.'.class%';
            $attrNode->appendChild($attrKey);

            if ($parent) {
                $attrKey = $dom->createAttribute('parent');
                $attrKey->value = $parent;
                $attrNode->appendChild($attrKey);
            }

            if ($scope) {
                $attrKey = $dom->createAttribute('scope');
                $attrKey->value = $scope;
                $attrNode->appendChild($attrKey);
            }

            if ($factoryService) {
                $attr = $dom->createAttribute('factory-service');
                $attr->value = $factoryService;
                $attrNode->appendChild($attr);
            }

            if ($factoryMethod) {
                $attr = $dom->createAttribute('factory-method');
                $attr->value = $factoryMethod;
                $attrNode->appendChild($attr);
            }

            foreach ($calls as $call) {
                $callNode = $dom->createElement('call');

                $attrKey = $dom->createAttribute('method');
                $attrKey->value = $call['method'];
                $callNode->appendChild($attrKey);

                $argNode = $dom->createElement('argument');

                $attrKey = $dom->createAttribute('type');
                $attrKey->value = 'service';
                $argNode->appendChild($attrKey);

                $attrKey = $dom->createAttribute('id');
                $attrKey->value = $call['service'];
                        
                $argNode->appendChild($attrKey);

                $callNode->appendChild($argNode);
                $attrNode->appendChild($callNode);
            }

            if ($tag) {
                $tagNode = $dom->createElement('tag');

                $attrKey = $dom->createAttribute('name');
                $attrKey->value = $tag;
                $tagNode->appendChild($attrKey);

                $attrNode->appendChild($tagNode);
            }

            foreach ($arguments as $argument) {
                $argNode = $dom->createElement('argument');

                $attrNode->appendChild($argNode);
            }

            $servicesNode->appendChild($attrNode);
        }

        return $dom;
    }
}
