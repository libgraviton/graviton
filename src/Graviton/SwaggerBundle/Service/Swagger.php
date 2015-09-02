<?php
/**
 * Generate swagger conform specs.
 */

namespace Graviton\SwaggerBundle\Service;

use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\SchemaBundle\Model\SchemaModel;
use Graviton\SchemaBundle\SchemaUtils;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Route;

/**
 * A service that generates a swagger conform service spec dynamically.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Swagger
{

    /**
     * @var \Graviton\RestBundle\Service\RestUtils
     */
    private $restUtils;

    /**
     * @var SchemaModel
     */
    private $schemaModel;

    /**
     * @var SchemaUtils
     */
    private $schemaUtils;

    /**
     * Constructor
     *
     * @param RestUtils   $restUtils   rest utils
     * @param SchemaModel $schemaModel schema model instance
     * @param SchemaUtils $schemaUtils schema utils
     */
    public function __construct(
        RestUtils $restUtils,
        SchemaModel $schemaModel,
        SchemaUtils $schemaUtils
    ) {
        $this->restUtils = $restUtils;
        $this->schemaModel = $schemaModel;
        $this->schemaUtils = $schemaUtils;
    }

    /**
     * Returns the swagger spec as array
     *
     * @return array Swagger spec
     */
    public function getSwaggerSpec()
    {
        $ret = $this->getBasicStructure();
        $routingMap = $this->restUtils->getServiceRoutingMap();
        $paths = array();

        foreach ($routingMap as $contName => $routes) {
            list(, $bundle,, $document) = explode('.', $contName);

            foreach ($routes as $routeName => $route) {
                $routeMethod = strtolower($route->getMethods()[0]);

                if ($routeMethod == 'options') {
                    continue;
                }

                // skip /schema/ stuff
                if (strpos($route->getPath(), '/schema/') !== false) {
                    list($pattern, $method, $data) = $this->getSchemaRoutes($route);
                    $paths[$pattern][$method] = $data;
                    continue;
                }

                $thisModel = $this->restUtils->getModelFromRoute($route);
                if ($thisModel === false) {
                    throw new \LogicException(
                        sprintf(
                            'Could not resolve route "%s" to model',
                            $routeName
                        )
                    );
                }

                $entityClassName = str_replace('\\', '', get_class($thisModel));

                $schema = $this->schemaUtils->getModelSchema($entityClassName, $thisModel);

                $ret['definitions'][$entityClassName] = json_decode(
                    $this->restUtils->serializeContent($schema),
                    true
                );

                $isCollectionRequest = true;
                if (in_array('id', array_keys($route->getRequirements())) === true) {
                    $isCollectionRequest = false;
                }

                $thisPattern = $route->getPattern();
                $entityName = ucfirst($document);

                $thisPath = $this->getBasicPathStructure(
                    $isCollectionRequest,
                    $entityName,
                    $entityClassName,
                    $schema->getProperty('id')->getType()
                );

                $thisPath['tags'] = $this->getPathTags($route);
                $thisPath['operationId'] = $routeName;
                $thisPath['summary'] = $this->getSummary($routeMethod, $isCollectionRequest, $entityName);

                // post body stuff
                if ($routeMethod == 'put' || $routeMethod == 'post') {
                    // special handling for POST/PUT.. we need to have 2 schemas, one for response, one for request..
                    // we don't want to have ID in the request body within those requests do we..
                    // an exception is when id is required..
                    $incomingEntitySchema = $entityClassName;
                    if (is_null($schema->getRequired()) || !in_array('id', $schema->getRequired())) {
                        $incomingEntitySchema = $incomingEntitySchema . 'Incoming';
                        $incomingSchema = clone $schema;
                        $incomingSchema->removeProperty('id');
                        $ret['definitions'][$incomingEntitySchema] = json_decode(
                            $this->restUtils->serializeContent($incomingSchema),
                            true
                        );
                    }

                    $thisPath['parameters'][] = array(
                        'name' => $bundle,
                        'in' => 'body',
                        'description' => 'Post',
                        'required' => true,
                        'schema' => array('$ref' => '#/definitions/' . $incomingEntitySchema)
                    );

                    // add error responses..
                    $thisPath['responses'][400] = array(
                        'description' => 'Bad request',
                        'schema' => array(
                            'type' => 'object'
                        )
                    );
                }

                $paths[$thisPattern][$routeMethod] = $thisPath;
            }
        }

        $ret['definitions']['SchemaModel'] = $this->schemaModel->getSchema();

        ksort($paths);
        $ret['paths'] = $paths;

        return $ret;
    }

    /**
     * Basic structure of the spec
     *
     * @return array Basic structure
     */
    private function getBasicStructure()
    {
        $ret = array();
        $ret['swagger'] = '2.0';
        $date = date('Y-m-d');
        $ret['info'] = array(
            // @todo this should be a real version - but should it be the version of graviton or which one?
            'version' => '0.1',
            'title' => 'Graviton REST Services',
            'description' => 'Testable API Documentation of this Graviton instance.',
            'lastUpdate' => $date
        );
        $ret['basePath'] = '/';
        $ret['schemes'] = array('http', 'https');

        return $ret;
    }

    /**
     * Return the basic structure of a path element
     *
     * @param bool   $isCollectionRequest if collection request
     * @param string $entityName          entity name
     * @param string $entityClassName     class name
     * @param string $idType              type of id field
     *
     * @return array Path spec
     */
    protected function getBasicPathStructure($isCollectionRequest, $entityName, $entityClassName, $idType)
    {
        $thisPath = array(
            'consumes' => array('application/json'),
            'produces' => array('application/json')
        );

        // collection return or not?
        if (!$isCollectionRequest) {
            // add object response
            $thisPath['responses'] = array(
                200 => array(
                    'description' => $entityName . ' response',
                    'schema' => array('$ref' => '#/definitions/' . $entityClassName)
                ),
                404 => array(
                    'description' => 'Resource not found'
                )
            );

            // add id param
            $thisPath['parameters'][] = array(
                'name' => 'id',
                'in' => 'path',
                'description' => 'ID of ' . $entityName . ' item to fetch/update',
                'required' => true,
                'type' => $idType
            );
        } else {
            // add array response
            $thisPath['responses'][200] = array(
                'description' => $entityName . ' response',
                'schema' => array(
                    'type' => 'array',
                    'items' => array('$ref' => '#/definitions/' . $entityClassName)
                )
            );
        }

        return $thisPath;
    }

    /**
     * Returns the tags (which influences the grouping visually) for a given route
     *
     * @param Route $route route
     * @param int   $part  part of route to use for generating a tag
     *
     * @return array Array of tags..
     */
    protected function getPathTags(Route $route, $part = 1)
    {
        $ret = array();
        $routeParts = explode('/', $route->getPath());
        if (isset($routeParts[$part])) {
            $ret[] = ucfirst($routeParts[$part]);
        }
        return $ret;
    }

    /**
     * Returns a meaningful summary depending on certain conditions
     *
     * @param string $method              Method
     * @param bool   $isCollectionRequest If collection request
     * @param string $entityName          Name of entity
     *
     * @return string summary
     */
    protected function getSummary($method, $isCollectionRequest, $entityName)
    {
        $ret = '';
        // meaningful descriptions..
        switch ($method) {
            case 'get':
                if ($isCollectionRequest) {
                    $ret = 'Get collection of ' . $entityName . ' resources';
                } else {
                    $ret = 'Get single ' . $entityName . ' resources';
                }
                break;
            case 'post':
                $ret = 'Create new ' . $entityName . ' resource';
                break;
            case 'put':
                $ret = 'Update existing ' . $entityName . ' resource';
                break;
            case 'delete':
                $ret = 'Delete existing ' . $entityName . ' resource';
        }
        return $ret;
    }

    /**
     * @param Route $route route
     *
     * @return array
     */
    protected function getSchemaRoutes(Route $route)
    {
        $path = $route->getPath();

        $describedService = substr(substr($path, 7), 0, substr($path, -5) == '/item' ? -7 : -10);

        $tags = array_merge(['Schema'], $this->getPathTags($route, 2));

        return [
            $path,
            'get',
            [
                'produces' => [
                    'application/json',
                ],
                'responses' => [
                    200 => [
                        'description' => 'JSON-Schema for ' . $describedService . '.',
                        'schema' => ['$ref' => '#/definitions/SchemaModel'],
                    ]
                ],
                'tags' => $tags,
                'summary' => 'Get schema information for ' . $describedService . ' endpoints.',
            ]
        ];
    }
}
