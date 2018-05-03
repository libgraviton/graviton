<?php
/**
 * generator code for resources
 */

namespace Graviton\GeneratorBundle\Generator;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldMapper;
use Graviton\GeneratorBundle\Generator\ResourceGenerator\ParameterBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * bundle containing various code generators
 *
 * This code is more or less loosley based on SensioBundleGenerator. It could
 * use some refactoring to duplicate less for that, but this is how i finally
 * got a working version.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ResourceGenerator extends AbstractGenerator
{
    /**
     * @private Filesystem
     */
    private $filesystem;

    /**
     * our json file definition
     *
     * @var JsonDefinition|null
     */
    private $json = null;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $parametersFile;

    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var string
     */
    protected $servicesFile;

    /**
     * @var FieldMapper
     */
    private $mapper;

    /**
     * @var boolean
     */
    private $generateController = false;

    /**
     * @var ParameterBuilder
     */
    private $parameterBuilder;

    /**
     * Instantiates generator object
     *
     * @param Filesystem       $filesystem       fs abstraction layer
     * @param FieldMapper      $mapper           field type mapper
     * @param ParameterBuilder $parameterBuilder parameter builder
     */
    public function __construct(
        Filesystem $filesystem,
        FieldMapper $mapper,
        ParameterBuilder $parameterBuilder
    ) {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->mapper = $mapper;
        $this->parameterBuilder = $parameterBuilder;
    }

    /**
     * @param JsonDefinition $json optional JsonDefinition object
     *
     * @return void
     */
    public function setJson(JsonDefinition $json)
    {
        $this->json = $json;
    }

    /**
     * @param boolean $generateController should the controller be generated or not
     *
     * @return void
     */
    public function setGenerateController($generateController)
    {
        $this->generateController = $generateController;
    }

    /**
     * generate the resource with all its bits and parts
     *
     * @param string $bundleDir       bundle dir
     * @param string $bundleNamespace bundle namespace
     * @param string $bundleName      bundle name
     * @param string $document        document name
     *
     * @return void
     */
    public function generate(
        $bundleDir,
        $bundleNamespace,
        $bundleName,
        $document
    ) {
        $this->readServicesAndParams($bundleDir);

        $basename = $this->getBundleBaseName($document);

        if (!is_null($this->json)) {
            $this->json->setNamespace($bundleNamespace);
        }

        // add more info to the fields array
        $mapper = $this->mapper;
        $fields = array_map(
            function ($field) use ($mapper) {
                return $mapper->map($field, $this->json);
            },
            $this->mapper->buildFields($this->json)
        );

        $parameters = $this->parameterBuilder
            ->reset()
            ->setParameter('document', $document)
            ->setParameter('base', $bundleNamespace)
            ->setParameter('bundle', $bundleName)
            ->setParameter('json', $this->json)
            ->setParameter('fields', $fields)
            ->setParameter('basename', $basename)
            ->setParameter('isrecordOriginFlagSet', $this->json->isRecordOriginFlagSet())
            ->setParameter('recordOriginModifiable', $this->json->isRecordOriginModifiable())
            ->setParameter('isVersioning', $this->json->isVersionedService())
            ->setParameter('collection', $this->json->getServiceCollection())
            ->setParameter('indexes', $this->json->getIndexes())
            ->setParameter('textIndexes', $this->json->getAllTextIndexes())
            ->getParameters();

        $this->generateDocument($parameters, $bundleDir, $document);
        $this->generateSerializer($parameters, $bundleDir, $document);
        $this->generateModel($parameters, $bundleDir, $document);

        if ($this->json instanceof JsonDefinition && $this->json->hasFixtures() === true) {
            $this->generateFixtures($parameters, $bundleDir, $document);
        }

        if ($this->generateController) {
            $this->generateController($parameters, $bundleDir, $document);
        }

        $this->persistServicesAndParams();
    }

    /**
     * reads the services.yml file
     *
     * @param string $bundleDir bundle dir
     *
     * @return void
     */
    protected function readServicesAndParams($bundleDir)
    {
        $this->servicesFile = $bundleDir.'/Resources/config/services.yml';
        if ($this->fs->exists($this->servicesFile)) {
            $this->services = Yaml::parseFile($this->servicesFile);
        }

        if (!isset($this->services['services'])) {
            $this->services['services'] = [];
        }

        if (isset($this->services['parameters'])) {
            $this->parameters['parameters'] = $this->services['parameters'];
            unset($this->services['parameters']);
        } else {
            $this->parameters['parameters'] = [];
        }
    }

    /**
     * writes the services.yml file which includes the params
     *
     * @return void
     */
    protected function persistServicesAndParams()
    {
        $this->filesystem->dumpFile(
            $this->servicesFile,
            Yaml::dump(array_merge($this->parameters, $this->services))
        );
    }

    /**
     * generate document part of a resource
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    protected function generateDocument($parameters, $dir, $document)
    {
        // doctrine mapping normal class
        $this->renderFile(
            'document/Document.mongodb.yml.twig',
            $dir . '/Resources/config/doctrine/' . $document . '.mongodb.yml',
            $parameters
        );

        // doctrine mapping embedded
        $this->renderFile(
            'document/Document.mongodb.yml.twig',
            $dir . '/Resources/config/doctrine/' . $document . 'Embedded.mongodb.yml',
            array_merge(
                $parameters,
                [
                    'document' => $document.'Embedded',
                    'docType' => 'embeddedDocument'
                ]
            )
        );

        $this->renderFile(
            'document/Document.php.twig',
            $dir . '/Document/' . $document . '.php',
            $parameters
        );
        $this->renderFile(
            'document/DocumentEmbedded.php.twig',
            $dir . '/Document/' . $document . 'Embedded.php',
            $parameters
        );
        $this->renderFile(
            'document/DocumentBase.php.twig',
            $dir . '/Document/' . $document . 'Base.php',
            $parameters
        );

        $this->generateServices($parameters, $dir, $document);
    }

    /**
     * update xml services
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    protected function generateServices($parameters, $dir, $document)
    {
        $bundleParts = explode('\\', $parameters['base']);
        $shortName = $bundleParts[0];
        $shortBundle = $this->getBundleBaseName($bundleParts[1]);

        $docName = implode(
            '.',
            array(
                strtolower($shortName),
                strtolower($shortBundle),
                'document',
                strtolower($parameters['document'])
            )
        );

        $this->addParameter(
            $parameters['base'] . 'Document\\' . $parameters['document'],
            $docName . '.class'
        );

        $this->addParameter(
            (array) $parameters['json']->getRoles(),
            $docName . '.roles'
        );

        $repoName = implode(
            '.',
            array(
                strtolower($shortName),
                strtolower($shortBundle),
                'repository',
                strtolower($parameters['document'])
            )
        );

        $this->addService(
            $repoName,
            null,
            [],
            null,
            array(
                array(
                    'type' => 'string',
                    'value' => $parameters['bundle'] . ':' . $document
                )
            ),
            'doctrine_mongodb.odm.default_document_manager',
            'getRepository',
            'Doctrine\ODM\MongoDB\DocumentRepository'
        );

        $this->addService(
            $repoName . 'embedded',
            null,
            [],
            null,
            array(
                array(
                    'type' => 'string',
                    'value' => $parameters['bundle'] . ':' . $document . 'Embedded'
                )
            ),
            'doctrine_mongodb.odm.default_document_manager',
            'getRepository',
            'Doctrine\ODM\MongoDB\DocumentRepository'
        );
    }

    /**
     * Registers information to be generated to a parameter tag.
     *
     * @param mixed  $value Content of the tag
     * @param string $key   Content of the key attribute
     *
     * @return void
     */
    protected function addParameter($value, $key)
    {
        $this->parameters['parameters'][$key] = $value;
        return $this->parameters['parameters'];
    }

    /**
     * add service to services.yml
     *
     * @param string $id             id of new service
     * @param string $parent         parent for service
     * @param array  $calls          methodCalls to add
     * @param string $tag            tag name or empty if no tag needed
     * @param array  $arguments      service arguments
     * @param string $factoryService factory service id
     * @param string $factoryMethod  factory method name
     * @param string $className      class name to override
     *
     * @return void
     */
    protected function addService(
        $id,
        $parent = null,
        array $calls = [],
        $tag = null,
        array $arguments = [],
        $factoryService = null,
        $factoryMethod = null,
        $className = null
    ) {
        $service = [];
        $service['public'] = true;

        // classname
        if (is_null($className)) {
            $className = '%' . $id . '.class%';
        }
        $service['class'] = $className;

        // parent
        if (!is_null($parent)) {
            $service['parent'] = $parent;
        }

        // factory
        if ($factoryService && $factoryMethod) {
            $service['factory'] = [
                '@'.$factoryService,
                $factoryMethod
            ];
        }

        foreach ($arguments as $argument) {
            if ($argument['type'] == 'service') {
                $service['arguments'][] = '@'.$argument['id'];
            } else {
                $service['arguments'][] = $argument['value'];
            }
        }

        // calls
        foreach ($calls as $call) {
            $service['calls'][] = [
                $call['method'],
                ['@'.$call['service']]
            ];
        }

        // tags
        if ($tag) {
            $thisTag = [
                'name' => $tag
            ];

            if ($this->json instanceof JsonDefinition) {
                $thisTag['collection'] = $this->json->getId();

                // is this read only?
                if ($this->json->isReadOnlyService()) {
                    $thisTag['read-only'] = true;
                }

                // router base defined?
                $routerBase = $this->json->getRouterBase();
                if ($routerBase !== false) {
                    $thisTag['router-base'] = $routerBase;
                }
            }

            $service['tags'][] = $thisTag;
        }

        $this->services['services'][$id] = $service;
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
        // @TODO in Embedded and document just render the differences..
        $this->renderFile(
            'serializer/Document.xml.twig',
            $dir . '/Resources/config/serializer/Document.' . $document . 'Embedded.xml',
            array_merge(
                $parameters,
                [
                    'document' => $document.'Embedded',
                    'noIdField' => true,
                    'realIdField' => true
                ]
            )
        );

        foreach ($parameters['fields'] as $key => $field) {
            if (substr($field['serializerType'], 0, 14) == 'array<Graviton' &&
                strpos($field['serializerType'], '\\Entity') === false &&
                $field['relType'] == 'embed'
            ) {
                $parameters['fields'][$key]['serializerType'] = substr($field['serializerType'], 0, -1).'Embedded>';
            } elseif (substr($field['serializerType'], 0, 8) == 'Graviton' &&
                strpos($field['serializerType'], '\\Entity') === false &&
                $field['relType'] == 'embed'
            ) {
                $parameters['fields'][$key]['serializerType'] = $field['serializerType'].'Embedded';
            }
        }
        $this->renderFile(
            'serializer/Document.xml.twig',
            $dir . '/Resources/config/serializer/Document.' . $document . '.xml',
            array_merge(
                $parameters,
                [
                    'realIdField' => false
                ]
            )
        );
        $this->renderFile(
            'serializer/Document.xml.twig',
            $dir . '/Resources/config/serializer/Document.' . $document . 'Base.xml',
            array_merge(
                $parameters,
                [
                    'document' => $document.'Base',
                    'realIdField' => false
                ]
            )
        );
    }

    /**
     * generate model part of a resource
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

        // embedded versions
        $this->renderFile(
            'model/Model.php.twig',
            $dir . '/Model/' . $document . 'Embedded.php',
            array_merge($parameters, ['document' => $document.'Embedded'])
        );
        $this->renderFile(
            'model/schema.json.twig',
            $dir . '/Resources/config/schema/' . $document . 'Embedded.json',
            array_merge($parameters, ['document' => $document.'Embedded'])
        );

        $bundleParts = explode('\\', $parameters['base']);
        $shortName = strtolower($bundleParts[0]);
        $shortBundle = strtolower(substr($bundleParts[1], 0, -6));
        $paramName = implode('.', array($shortName, $shortBundle, 'model', strtolower($parameters['document'])));
        $repoName = implode('.', array($shortName, $shortBundle, 'repository', strtolower($parameters['document'])));

        $this->addParameter($parameters['base'] . 'Model\\' . $parameters['document'], $paramName . '.class');

        // normal service
        $this->addService(
            $paramName,
            'graviton.rest.model',
            array(
                [
                    'method' => 'setRepository',
                    'service' => $repoName
                ],
            ),
            null
        );

        // embedded service
        $this->addParameter(
            $parameters['base'] . 'Model\\' . $parameters['document'] . 'Embedded',
            $paramName . 'embedded.class'
        );

        $this->addService(
            $paramName . 'embedded',
            'graviton.rest.model',
            array(
                [
                    'method' => 'setRepository',
                    'service' => $repoName . 'embedded'
                ],
            ),
            null
        );
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

        $bundleParts = explode('\\', $parameters['base']);
        $shortName = strtolower($bundleParts[0]);
        $shortBundle = strtolower(substr($bundleParts[1], 0, -6));
        $paramName = implode('.', array($shortName, $shortBundle, 'controller', strtolower($parameters['document'])));

        $this->addParameter(
            $parameters['base'] . 'Controller\\' . $parameters['document'] . 'Controller',
            $paramName . '.class'
        );

        $this->addService(
            $paramName,
            $parameters['parent'],
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
    }

    /**
     * generates fixtures
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    protected function generateFixtures(array $parameters, $dir, $document)
    {
        $parameters['fixtures_json'] = addcslashes(json_encode($this->json->getFixtures()), "'");
        $parameters['fixtureOrder'] = $this->json->getFixtureOrder();

        $this->renderFile(
            'fixtures/LoadFixtures.php.twig',
            $dir . '/DataFixtures/MongoDB/Load' . $document . 'Data.php',
            $parameters
        );
    }
}
