<?php
/**
 * provides accessors to the analytics services
 */

namespace Graviton\AnalyticsBundle\Manager;

use Graviton\AnalyticsBundle\Exception\AnalyticUsageException;
use Graviton\AnalyticsBundle\Helper\JsonMapper;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Graviton\DocumentBundle\Service\DateConverter;
use Graviton\GeneratorBundle\Event\GenerateSchemaEvent;
use Graviton\GeneratorBundle\Generator\SchemaGenerator;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;

/**
 * Service Request Converter and startup for Analytics
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class ServiceManager
{

    /**
     * ServiceConverter constructor.
     *
     * @param RequestStack     $requestStack      Sf Request information service
     * @param AnalyticsManager $analyticsManager  Db Manager and query control
     * @param DateConverter    $dateConverter     date converter
     * @param Router           $router            To manage routing generation
     * @param array            $analyticsServices the services
     */
    public function __construct(
        private RequestStack $requestStack,
        private AnalyticsManager $analyticsManager,
        private DateConverter $dateConverter,
        private Router $router,
        private array $analyticsServices,
        private JsonMapper $jsonMapper = new JsonMapper()
    ) {
    }

    /**
     * Return array of available services
     *
     * @return array
     */
    public function getServices() : array
    {
        $services = [];
        foreach ($this->analyticsServices as $name => $service) {
            if (is_numeric($name)) {
                continue;
            }

            $services[] = [
                '$ref' => $this->router->generate(
                    'graviton_analytics_service',
                    [
                        'service' => $service['route']
                    ],
                    true
                ),
                'api-docs' => [
                    'json' => [
                        '$ref' => $this->router->generate(
                            'graviton_analytics_service_schema',
                            [
                                'service' => $service['route'],
                                'format' => 'json'
                            ],
                            true
                        )
                    ],
                    'yaml' => [
                        '$ref' => $this->router->generate(
                            'graviton_analytics_service_schema',
                            [
                                'service' => $service['route'],
                                'format' => 'yaml'
                            ],
                            true
                        )
                    ],
                ]
            ];
        }

        return $services;
    }

    /**
     * Get service definition
     *
     * @param string $name Route name for service
     *
     * @throws NotFoundHttpException
     * @return AnalyticModel
     */
    public function getAnalyticModel(string $name) : AnalyticModel
    {
        if (!isset($this->analyticsServices[$name])) {
            throw new NotFoundHttpException(
                sprintf('Analytic definition "%s" was not found', $name)
            );
        }

        return $this->jsonMapper->map($this->analyticsServices[$name], new AnalyticModel());
    }

    /**
     * Will map and find data for defined route
     *
     * @return array
     */
    public function getData(string $modelName)
    {
        $model = $this->getAnalyticModel($modelName);
        return $this->analyticsManager->getData($model, $this->getServiceParameters($model));
    }

    /**
     * Locate and display service definition schema
     *
     * @param string $service service
     *
     * @return array schema
     */
    public function getSchema(string $service) : array
    {
        // Locate the schema definition
        $model = $this->getAnalyticModel($service);

        $endpoint = sprintf('/analytics/%s', $service);
        $docName = sprintf('Analytical%s', ucfirst(strtolower($service)));

        $base = [
            'openapi' => SchemaGenerator::OPENAPI_VERSION,
            'info' => [
                'title' => 'Analytical endpoint "'.$service.'".',
            ],
            'paths' => [],
            'components' => [
                'schemas' => []
            ]
        ];

        $parameters = [];
        foreach ($model->getParams() as $param) {
            $paramType = $param['type'];
            $format = null;
            if ($paramType == 'datetime') {
                $paramType = 'string';
                $format = 'date-time';
            }
            if ($paramType == 'varchar') {
                $paramType = 'string';
            }

            $queryParam = [
                'in' => 'query',
                'name' => $param['name'],
                'schema' => [
                    'type' => $paramType
                ],
                'required' => (isset($param['required']) && $param['required'] === true)
            ];

            if (isset($param['default'])) {
                $queryParam['schema']['default'] = $param['default'];
            }
            if (!empty($format)) {
                $queryParam['schema']['format'] = $format;
            }

            $parameters[] = $queryParam;
        }

        $base['paths'][$endpoint]['get'] = [
            'summary' => 'Gets the analytical data.',
            'operationId' => 'analyticalGet'.ucfirst(strtolower($service)),
            'responses' => [
                200 => [
                    'description' => 'successful operation',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/'.$docName]
                        ]
                    ]
                ],
                400 => [
                    'description' => 'Invalid parameter supplied.',
                ]
            ],
            'parameters' => $parameters
        ];

        $mainType = $model->getType();

        // the type itself!
        $schema = $model->getSchema();
        $document = [
            'type' => 'object',
            'properties' => []
        ];

        if (isset($schema['title'])) {
            $document['title'] = $schema['title'];
        }
        if (isset($schema['description'])) {
            $document['description'] = $schema['description'];
        }
        if (isset($schema['type'])) {
            $document['type'] = $mainType;
        }
        if (is_array($schema['properties'])) {
            if ($mainType == 'array') {
                $document['items'] = [
                    'type' => $schema['type'],
                    'properties' => $schema['properties']
                ];
            } else {
                $document['properties'] = $schema['properties'];
            }
        }

        $base['components']['schemas'][$docName] = $document;

        return $base;
    }

    /**
     * we subscribed here to add our analytics route schema to the global openapi schema file!
     *
     * @param GenerateSchemaEvent $event event
     *
     * @return void
     */
    public function onSchemaGeneration(GenerateSchemaEvent $event)
    {
        // iterate all services
        foreach ($this->analyticsServices as $name => $service) {
            $event->addSingleSchema(
                $this->getSchema($name)
            );
        }
    }

    /**
     * returns the params as passed from the user
     *
     * @param AnalyticModel $model model
     *
     * @return array the params, converted as specified
     * @throws AnalyticUsageException
     */
    private function getServiceParameters(AnalyticModel $model)
    {
        $params = [];
        if (!is_array($model->getParams())) {
            return $params;
        }

        foreach ($model->getParams() as $param) {
            if (!isset($param['name'])) {
                throw new \LogicException("Incorrect spec (no name) of param in analytics route " . $model->getRoute());
            }

            $paramValue = $this->requestStack->getCurrentRequest()->query->get($param['name'], null);

            // default set?
            if (is_null($paramValue) && isset($param['default'])) {
                $paramValue = $param['default'];
            }

            // required missing?
            if (is_null($paramValue) && (isset($param['required']) && $param['required'] === true)) {
                throw new AnalyticUsageException(
                    sprintf(
                        "Missing parameter '%s' in analytics route '%s'",
                        $param['name'],
                        $model->getRoute()
                    )
                );
            }

            if (!is_null($param['type']) && !is_null($paramValue)) {
                switch ($param['type']) {
                    case "integer":
                        $paramValue = intval($paramValue);

                        // more than max? limit to max..
                        if (isset($param['max']) && is_numeric($param['max']) && intval($param['max']) < $paramValue) {
                            $paramValue = intval($param['max']);
                        }
                        break;
                    case "boolean":
                        if ($paramValue === 'true') {
                            $paramValue = true;
                        } elseif ($paramValue === 'false') {
                            $paramValue = false;
                        } else {
                            $paramValue = boolval($paramValue);
                        }
                        break;
                    case "array":
                        $paramValue = explode(',', $paramValue);
                        break;
                    case "date":
                        $paramValue = new UTCDateTime($this->dateConverter->getDateTimeFromString($paramValue));
                        break;
                    case "regex":
                        $paramValue = new Regex($paramValue, 'i');
                        break;
                    case "regexstring":
                        $paramValue = '^'.str_replace('*', '(.*)', $paramValue);
                        break;
                    case "mongoid":
                        $paramValue = new ObjectId($paramValue);
                        break;
                    case "array<integer>":
                        $paramValue = array_map('intval', explode(',', $paramValue));
                        break;
                    case "array<boolean>":
                        $paramValue = array_map('boolval', explode(',', $paramValue));
                        break;
                }
            }

            if (!is_null($paramValue)) {
                $params[$param['name']] = $paramValue;
            }
        }

        return $params;
    }
}
