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
        $response = Response::getResponse(200);

        $router = $this->container->get('router');
        $serializer = $this->container->get('serializer');

        // match with random id to get route
        $route = $router->match('/'.$id.'/1234');
        list($app, $module, $type, $model, $action) = explode('.', $route['_route']);

        $modelName = $model;
        $model = $this->container->get(implode('.', array($app, $module, 'model', $model)));

        // build up schema data
        $schema = new \stdClass;
        $schema->title = ucfirst($modelName);
        $schema->description = $model->getDescription();
        $schema->type = 'object';
        $schema->properties = new \stdClass;

        // grab schema info from model
        $repo = $model->getRepository();
        $meta = $repo->getClassMetadata();

        foreach ($meta->getFieldNames() as $field) {
            $schema->properties->$field = new \stdClass;
            $schema->properties->$field->type = $meta->getTypeOfField($field);
            $schema->properties->$field->description = $model->getDescriptionOfField($field);
        }
        $schema->required = $model->getRequiredFields();

        $response->setContent(json_encode($schema));

        // set content type to match schema
        $dottedModel = strtr($id, '/', '.');
        $type = sprintf('application/vnd.graviton.schema.%s+json; charset=UTF-8', $dottedModel);
        $response->headers->set('Content-Type', $type);

        return $response;
    }
}
