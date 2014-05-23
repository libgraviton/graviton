<?php

namespace Graviton\SchemaBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Graviton\RestBundle\Response\ResponseFactory as Response;

class SchemaController implements ContainerAwareInterface
{
    private $container;

    /**
     * {@inheritdoc}
     *
     * @param ContainerInterface $container service_container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
