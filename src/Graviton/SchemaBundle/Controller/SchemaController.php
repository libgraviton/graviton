<?php

namespace Graviton\SchemaBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Graviton\RestBundle\Response\ResponseFactory as Response;

/**
 * Controller for acccessing schema information
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SchemaController implements ContainerAwareInterface
{
    private $container;

    /**
     * {@inheritdoc}
     *
     * @param ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * return the schema for a given route
     *
     * @param String $id path to the route (called id for consistency with other modules)
     *
     * @return Response
     */
    public function getAction($id)
    {
        $response = Response::getResponse(404, 'Schema for /'.$id.' not found');

        $router = $this->container->get('router');
        $serializer = $this->container->get('serializer');

        $match = $router->match('/'.$id);

        $dottedModel = strtr($id, '/', '.');
        $type = sprintf('application/vnd.graviton.schema.%s+json; charset=UTF-8', $dottedModel);
        $response->headers->set('Content-Type', $type);

        return $response;
    }
}
