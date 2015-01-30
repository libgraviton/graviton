<?php

namespace Graviton\RestBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This is a basic rest controller. It should fit the most needs but if you need to add some
 * extra functionality you can extend it and overwrite single/all actions.
 * You can also extend the model class to add some extra logic before save
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
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
            'version' => '0.1',
            'title' => 'Graviton REST Services'
        );
        $ret['host'] = $_SERVER['HTTP_HOST'];
        $ret['basePath'] = '/';
        $ret['schemes'] = array('http');

        $restUtils = $this->container->get('graviton.rest.restutils');
        /** @var $collection \Symfony\Component\Routing\RouteCollection */
        $optionRoutes = $restUtils->getOptionRoutes();
        $routingMap = $restUtils->getServiceRoutingMap();
        $paths = array();

        foreach ($routingMap as $contName => $routes) {
            foreach ($routes as $routeName => $route) {
                $thisPattern = $route->getPattern();
                $thisMethod = $route->getMethods()[0];

                $thisPath = array(
                    'summary' => 'Some summary',
                    'description' => '',
                    'operationId' => $routeName,
                    'consumes' => array('application/json'),
                    'produces' => array('application/json')
                );

                $paths[$thisPattern][strtolower($thisMethod)] = $thisPath;
            }
        }

        $ret['paths'] = $paths;

        $response->setContent(json_encode($ret));
        return $response;
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

    /**
     * Get the container object
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */

    public function getContainer()
    {
        return $this->container;
    }

}
