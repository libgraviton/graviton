<?php
/**
 * Generate swagger conform specs.
 */

namespace Graviton\SwaggerBundle\Service;

use Graviton\SchemaBundle\SchemaUtils;

/**
 * A service that generates a swagger conform service spec dynamically.
 *
 * @category GravitonSwaggerBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Swagger
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * @var \Graviton\RestBundle\Service\RestUtils
     */
    private $restUtils;

    /**
     * sets the container
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer($container = null)
    {
        $this->container = $container;
    }

    /**
     * sets restUtils
     *
     * @param \Graviton\RestBundle\Service\RestUtils $restUtils rest utils
     *
     * @return void
     */
    public function setRestUtils($restUtils = null)
    {
        $this->restUtils = $restUtils;
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
            list($app, $bundle, $rest, $document) = explode('.', $contName);

            foreach ($routes as $routeName => $route) {
                $routeMethod = strtolower($route->getMethods()[0]);

                // skip PATCH (as for now) & /schema/ stuff
                if (strpos($route->getPath(), '/schema/') !== false ||
                    $routeMethod == 'options' ||
                    $routeMethod == 'patch'
                ) {
                    continue;
                }

                $thisModel = $this->restUtils->getModelFromRoute($route);
                $entityClassName = str_replace('\\', '', get_class($thisModel));

                $schema = SchemaUtils::getModelSchema($entityClassName, $thisModel, array(), array());

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

                $thisPath = array(
                    'tags' => array(ucfirst($bundle)),
                    'operationId' => $routeName,
                    'consumes' => array('application/json'),
                    'produces' => array('application/json')
                );

                // meaningful descriptions..
                switch ($routeMethod) {
                    case 'get':
                        if ($isCollectionRequest) {
                            $thisPath['summary'] = 'Get collection of ' . $entityName . ' resources';
                        } else {
                            $thisPath['summary'] = 'Get single ' . $entityName . ' resources';
                        }
                        break;
                    case 'post':
                        $thisPath['summary'] = 'Create new ' . $entityName . ' resource';
                        break;
                    case 'put':
                        $thisPath['summary'] = 'Update existing ' . $entityName . ' resource';
                        break;
                    case 'delete':
                        $thisPath['summary'] = 'Delete existing ' . $entityName . ' resource';
                }

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
                        'type' => $schema->getProperty('id')
                                         ->getType()
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

                    /* not yet ;-(
                    $thisPath['parameters'][] = array(
                        'name' => 'filter',
                        'in' => 'query',
                        'description' => 'Optional RQL filter',
                        'required' => false,
                        'default' => '',
                        'type' => 'string'
                    );
                    */

                    $thisPath['parameters'][] = array(
                        'name' => 'q',
                        'in' => 'query',
                        'description' => 'Optional RQL filter',
                        'required' => false,
                        'type' => 'string'
                    );
                    // paging params
                    $thisPath['parameters'][] = array(
                        'name' => 'page',
                        'in' => 'query',
                        'description' => '(Paging) Page to fetch',
                        'required' => false,
                        'default' => 1,
                        'type' => 'integer'
                    );
                    $thisPath['parameters'][] = array(
                        'name' => 'perPage',
                        'in' => 'query',
                        'description' => '(Paging) Items per page',
                        'required' => false,
                        'default' => 10,
                        'type' => 'integer'
                    );
                }

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
        $ret['info'] = array(
            // @todo this should be a real version - but should it be the version of graviton or which one?
            'version' => '0.1',
            'title' => 'Graviton REST Services',
            'description' => 'Testable API Documentation of this Graviton instance.'
        );
        $ret['host'] = $_SERVER['HTTP_HOST'];
        $ret['basePath'] = '/';
        $ret['schemes'] = array('http');

        return $ret;
    }
}
