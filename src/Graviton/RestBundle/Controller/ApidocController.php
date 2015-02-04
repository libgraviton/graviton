<?php

namespace Graviton\RestBundle\Controller;

use Graviton\RestBundle\Service\RestUtils;
use Graviton\SchemaBundle\SchemaUtils;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This is a basic rest controller. It should fit the most needs but if you need to add some
 * extra functionality you can extend it and overwrite single/all actions.
 * You can also extend the model class to add some extra logic before save
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ApidocController implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * Returns a single record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function indexAction()
    {

        $response = $this->container->get("graviton.rest.response");

        $ret = array();
        $ret['swagger'] = '2.0';
        $ret['info'] = array(
            'description' => 'Description',
            // @todo this should be a real version - but should it be the version of graviton or which one?
            'version' => '0.1',
            'title' => 'Graviton REST Services'
        );
        $ret['host'] = $_SERVER['HTTP_HOST'];
        $ret['basePath'] = '/';
        $ret['schemes'] = array('http');

        /** @var $restUtils RestUtils */
        $restUtils = $this->container->get('graviton.rest.restutils');
        $routingMap = $restUtils->getServiceRoutingMap();
        $paths = array();

        foreach ($routingMap as $contName => $routes) {

            list($app, $bundle, $rest, $document) = explode('.', $contName);

            foreach ($routes as $routeName => $route) {

                $routeMethod = strtolower($route->getMethods()[0]);

                // skip PATCH (as for now) & /schema/ stuff
                if (strpos(
                        $route->getPath(),
                        '/schema/'
                    ) !== false || $routeMethod == 'options' || $routeMethod == 'patch'
                ) {
                    continue;
                }

                $thisModel = $restUtils->getModelFromRoute($route);
                $entityClassName = str_replace('\\', '', get_class($thisModel));

                $schema = SchemaUtils::getModelSchema($entityClassName, $thisModel, array(), array());

                $ret['definitions'][$entityClassName] = json_decode(
                    $restUtils->getControllerFromRoute($route)
                              ->serializeContent($schema),
                    true
                );

                $isCollectionRequest = true;
                if (in_array('id', array_keys($route->getRequirements())) === true) {
                    $isCollectionRequest = false;
                }

                $thisPattern = $route->getPattern();
                $entityName = ucfirst($document);

                $thisPath = array(
                    'summary' => 'Some summary',
                    'tags' => array(ucfirst($bundle)),
                    'operationId' => $routeName,
                    'consumes' => array('application/json'),
                    'produces' => array('application/json')
                );

                // meaningful descriptions..
                switch ($routeMethod) {
                    case 'get':
                        if ($isCollectionRequest) {
                            $thisPath['summary'] = 'Get collection of ' . $entityName . ' objects';
                        } else {
                            $thisPath['summary'] = 'Get single ' . $entityName . ' object';
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
                }

                // post body stuff
                if ($routeMethod == 'put' || $routeMethod == 'post') {

                    // special handling for POST/PUT.. we need to have 2 schemas, one for response, one for request..
                    // we don't want to have ID in the request body within those requests do we..
                    // an exception is when id is required..
                    $incomingEntitySchema = $entityClassName;
                    if (!in_array('id', $schema->getRequired())) {
                        $incomingEntitySchema = $incomingEntitySchema . 'Incoming';
                        $incomingSchema = clone $schema;
                        $incomingSchema->removeProperty('id');
                        $ret['definitions'][$incomingEntitySchema] = json_decode(
                            $restUtils->getControllerFromRoute($route)
                                      ->serializeContent($incomingSchema),
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
        //die;
        $ret['paths'] = $paths;
        $response->setContent(json_encode($ret));

        return $response;
    }

    /**
     * Get the container object
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
