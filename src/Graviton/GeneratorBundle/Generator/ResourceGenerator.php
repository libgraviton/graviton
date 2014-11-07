<?php

namespace Graviton\GeneratorBundle\Generator;

use Doctrine\Common\Inflector\Inflector;
use Graviton\GeneratorBundle\Definition\DefinitionElementInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
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
 *
 * @todo     split all the xml handling on services.conf into a Manipulator
 */
class ResourceGenerator extends AbstractGenerator
{
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
     * @private
     */
    private $input;

    /**
     * our json file definition
     *
     * @var JsonDefinition
     */
    private $json = false;

    /**
     * instanciate generator object
     *
     * @param InputInterface $input      Input
     * @param FileSystem     $filesystem fs abstraction layer
     * @param object         $doctrine   dbal
     * @param object         $kernel     app kernel
     *
     * @return ResourceGenerator
     */
    public function __construct($input, $filesystem, $doctrine, $kernel)
    {
        $this->input = $input;
        $this->filesystem = $filesystem;
        $this->doctrine = $doctrine;
        $this->kernel = $kernel;
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

        // do we have a json path passed?
        if (!is_null($this->input->getOption('json'))) {
            $this->json = new JsonDefinition($this->input->getOption('json'));
        }

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

                // add information from our json file (if provided)..
                if (
                    $this->json instanceof JsonDefinition &&
                    $this->json->getField($field['fieldName']) instanceof DefinitionElementInterface
                ) {
                    $fieldInformation = $this->json->getField($field['fieldName'])
                                                   ->getDefAsArray();

                    // in this context, the default type is the doctrine type..
                    if (isset($fieldInformation['doctrineType'])) {
                        $fieldInformation['type'] = $fieldInformation['doctrineType'];
                    }

                    $field = array_merge($field, $fieldInformation);
                }

                return $field;
            },
            $fields
        );

        $parameters = array(
            'document' => $document,
            'base' => $bundleNamespace,
            'bundle' => $bundle->getName(),
            'format' => $format,
            'author' => $author,
            'email' => $email,
            'json' => $this->json,
            'fields' => $fields,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );

        // some stuff special for the "id" field..
        if ($this->json instanceof JsonDefinition) {
            // if we have data for id field, pass it along
            $idField = $this->json->getField('id');
            if (!is_null($idField)) {
                $parameters['idField'] = $idField->getDefAsArray();
            } else {
                // if there is a json file and no id defined - so we don't do one here..
                // we leave it in the document though but we don't wanna output it..
                $parameters['noIdField'] = true;
            }
        }

        $this->generateDocument($parameters, $dir, $document, $withRepository);
        $this->generateSerializer($parameters, $dir, $document);
        $this->generateModel($parameters, $dir, $document);

        if ($this->input->getOption('no-controller') != 'true') {
            $this->generateController($parameters, $dir, $document);
        }
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
            $dir . '/Resources/config/doctrine/' . $document . '.mongodb.xml',
            $parameters
        );

        $this->renderFile(
            'document/Document.php.twig',
            $dir . '/Document/' . $document . '.php',
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
            $docName . '.class',
            $parameters['base'] . 'Document\\' . $parameters['document']
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
                $repoName . '.class',
                $parameters['base'] . 'Repository\\' . $parameters['document']
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
                        'value' => $parameters['bundle'] . ':' . $document
                    )
                ),
                'doctrine_mongodb.odm.default_document_manager',
                'getRepository'
            );

            $this->renderFile(
                'document/DocumentRepository.php.twig',
                $dir . '/Repository/' . $document . 'Repository.php',
                $parameters
            );
        }

        file_put_contents($dir . '/Resources/config/services.xml', $services->saveXML());
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
        $services->load($dir . '/Resources/config/services.xml');

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
        $paramNode = $this->addNodeIfMissing($dom, 'parameters');

        $xpath = new \DomXpath($dom);

        $nodes = $xpath->query('//parameters/parameter[@key="' . $key . '.class"]');
        if ($nodes->length < 1) {
            $attrNode = $dom->createElement('parameter', $value);

            $this->addAttributeToNode('key', $key, $dom, $attrNode);

            $paramNode->appendChild($attrNode);
        }

        return $dom;
    }

    /**
     * add node if missing
     *
     * @param \DOMDocument &$dom      document
     * @param string       $element   name for new node element
     * @param string       $container name of container tag
     *
     * @return \DOMNode new element node
     */
    private function addNodeIfMissing(&$dom, $element, $container = 'container')
    {
        $container = $dom->getElementsByTagName($container)
                         ->item(0);
        $nodes = $dom->getElementsByTagName($element);
        if ($nodes->length < 1) {
            $newNode = $dom->createElement($element);
            $container->appendChild($newNode);
        } else {
            $newNode = $nodes->item(0);
        }

        return $newNode;
    }

    /**
     * add attribute to node if needed
     *
     * @param string       $name  attribute name
     * @param string       $value attribute value
     * @param \DOMDocument $dom   document
     * @param \DOMElement  $node  parent node
     *
     * @return void
     */
    private function addAttributeToNode($name, $value, $dom, $node)
    {
        if ($value) {
            $attr = $dom->createAttribute($name);
            $attr->value = $value;
            $node->appendChild($attr);
        }
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
        $servicesNode = $this->addNodeIfMissing($dom, 'services');

        $xpath = new \DomXpath($dom);

        // add controller to services
        $nodes = $xpath->query('//services/service[@id="' . $id . '"]');
        if ($nodes->length < 1) {
            $attrNode = $dom->createElement('service');

            $this->addAttributeToNode('id', $id, $dom, $attrNode);
            $this->addAttributeToNode('class', '%' . $id . '.class%', $dom, $attrNode);
            $this->addAttributeToNode('parent', $parent, $dom, $attrNode);
            $this->addAttributeToNode('scope', $scope, $dom, $attrNode);
            $this->addAttributeToNode('factory-service', $factoryService, $dom, $attrNode);
            $this->addAttributeToNode('factory-method', $factoryMethod, $dom, $attrNode);
            $this->addCallsToService($calls, $dom, $attrNode);

            if ($tag) {
                $tagNode = $dom->createElement('tag');

                $this->addAttributeToNode('name', $tag, $dom, $tagNode);

                // get stuff from json definition
                if ($this->json instanceof JsonDefinition) {
                    // is this read only?
                    if ($this->json->isReadOnlyService()) {
                        $this->addAttributeToNode('read-only', 'true', $dom, $tagNode);
                    }

                    // router base defined?
                    $routerBase = $this->json->getRouterBase();
                    if ($routerBase !== false) {
                        $this->addAttributeToNode('router-base', $routerBase, $dom, $tagNode);
                    }
                }

                $attrNode->appendChild($tagNode);
            }

            $this->addArgumentsToService($arguments, $dom, $attrNode);

            $servicesNode->appendChild($attrNode);
        }

        return $dom;
    }

    /**
     * add calls to service
     *
     * @param array        $calls info on calls to create
     * @param \DOMDocument $dom   current domdocument
     * @param \DOMElement  $node  node to add call to
     *
     * @return void
     */
    private function addCallsToService($calls, $dom, $node)
    {
        foreach ($calls as $call) {
            $this->addCallToService($call, $dom, $node);
        }
    }

    /**
     * add call to service
     *
     * @param array        $call info on call node to create
     * @param \DOMDocument $dom  current domdocument
     * @param \DOMElement  $node node to add call to
     *
     * @return void
     */
    private function addCallToService($call, $dom, $node)
    {
        $callNode = $dom->createElement('call');

        $attr = $dom->createAttribute('method');
        $attr->value = $call['method'];
        $callNode->appendChild($attr);

        $argNode = $dom->createElement('argument');

        $attr = $dom->createAttribute('type');
        $attr->value = 'service';
        $argNode->appendChild($attr);

        $attr = $dom->createAttribute('id');
        $attr->value = $call['service'];
        $argNode->appendChild($attr);

        $callNode->appendChild($argNode);

        $node->appendChild($callNode);
    }

    /**
     * add arguments to servie
     *
     * @param array        $arguments arguments to create
     * @param \DOMDocument $dom       dom document to add to
     * @param \DOMElement  $node      node to use as parent
     *
     * @return void
     */
    private function addArgumentsToService($arguments, $dom, $node)
    {
        foreach ($arguments as $argument) {
            $this->addArgumentToService($argument, $dom, $node);
        }
    }

    /**
     * add argument to service
     *
     * @param array        $argument info on argument to create
     * @param \DOMDocument $dom      dom document to add to
     * @param \DOMElement  $node     node to use as parent
     *
     * @return void
     */
    private function addArgumentToService($argument, $dom, $node)
    {
        $argNode = $dom->createElement('argument', $argument['value']);

        $argType = $dom->createAttribute('type');
        $argType->value = $argument['type'];
        $argNode->appendChild($argType);

        $node->appendChild($argNode);
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
            $dir . '/Resources/config/serializer/Document.' . $document . '.xml',
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
            $dir . '/Model/' . $document . '.php',
            $parameters
        );

        $this->renderFile(
            'model/schema.json.twig',
            $dir . '/Resources/config/schema/' . $document . '.json',
            $parameters
        );

        $this->renderFile(
            'validator/validation.xml.twig',
            $dir . '/Resources/config/validation.xml',
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
            $paramName . '.class',
            $parameters['base'] . 'Model\\' . $parameters['document']
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

        file_put_contents($dir . '/Resources/config/services.xml', $services->saveXML());
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
            $dir . '/Controller/' . $document . 'Controller.php',
            $parameters
        );

        $services = $this->loadServices($dir);

        $bundleParts = explode('\\', $parameters['base']);
        $shortName = strtolower($bundleParts[0]);
        $shortBundle = strtolower(substr($bundleParts[1], 0, -6));
        $paramName = implode('.', array($shortName, $shortBundle, 'controller', strtolower($parameters['document'])));

        $services = $this->addParam(
            $services,
            $paramName . '.class',
            $parameters['base'] . 'Controller\\' . $parameters['document'] . 'Controller'
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

        file_put_contents($dir . '/Resources/config/services.xml', $services->saveXML());
    }
}
