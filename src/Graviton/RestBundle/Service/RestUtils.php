<?php
/**
 * service for RESTy stuff
 */

namespace Graviton\RestBundle\Service;

use Graviton\JsonSchemaBundle\Validator\Validator;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\SchemaUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Graviton\RestBundle\Controller\RestController;

/**
 * A service (meaning symfony service) providing some convenience stuff when dealing with our RestController
 * based services (meaning rest services).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class RestUtils implements RestUtilsInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var null|SerializationContext
     */
    private $serializerContext;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SchemaUtils
     */
    private $schemaUtils;

    /**
     * @var Validator
     */
    private $schemaValidator;

    /**
     * @param ContainerInterface   $container         container
     * @param Router               $router            router
     * @param Serializer           $serializer        serializer
     * @param LoggerInterface      $logger            PSR logger (e.g. Monolog)
     * @param SerializationContext $serializerContext context for serializer
     */
    public function __construct(
        ContainerInterface $container,
        Router $router,
        Serializer $serializer,
        LoggerInterface $logger,
        SerializationContext $serializerContext = null,
        SchemaUtils $schemaUtils,
        Validator $schemaValidator
    ) {
        $this->container = $container;
        $this->serializer = $serializer;
        $this->serializerContext = $serializerContext;
        $this->router = $router;
        $this->logger = $logger;
        $this->schemaUtils = $schemaUtils;
        $this->schemaValidator = $schemaValidator;
    }

    /**
     * Builds a map of baseroutes (controllers) to its relevant route to the actions.
     * ignores schema stuff.
     *
     * @return array grouped array of basenames and actions..
     */
    public function getServiceRoutingMap()
    {
        $ret = array();
        $optionRoutes = $this->getOptionRoutes();

        foreach ($optionRoutes as $routeName => $optionRoute) {
            // get base name from options action
            $routeParts = explode('.', $routeName);
            array_pop($routeParts); // get rid of last part
            $baseName = implode('.', $routeParts);

            // get routes from same controller
            foreach ($this->getRoutesByBasename($baseName) as $routeName => $route) {
                // don't put schema stuff
                if (strpos('schema', strtolower($routeName)) === false) {
                    $ret[$baseName][$routeName] = $route;
                }
            }
        }

        return $ret;
    }

    /**
     * Public function to serialize stuff according to the serializer rules.
     *
     * @param object $content Any content to serialize
     * @param string $format  Which format to serialize into
     *
     * @throws \Exception
     *
     * @return string $content Json content
     */
    public function serializeContent($content, $format = 'json')
    {
        try {
            return $this->getSerializer()->serialize(
                $content,
                $format,
                $this->getSerializerContext()
            );
        } catch (\Exception $e) {
            $msg = sprintf(
                'Cannot serialize content class: %s; with id: %s; Message: %s',
                get_class($content),
                method_exists($content, 'getId') ? $content->getId() : '-no id-',
                str_replace('MongoDBODMProxies\__CG__\GravitonDyn', '', $e->getMessage())
            );
            $this->logger->alert($msg);
            throw new \Exception($msg, $e->getCode());
        }
    }

    /**
     * Deserialize the given content throw an exception if something went wrong
     *
     * @param string $content       Request content
     * @param string $documentClass Document class
     * @param string $format        Which format to deserialize from
     *
     * @throws \Exception
     *
     * @return object|array|integer|double|string|boolean
     */
    public function deserializeContent($content, $documentClass, $format = 'json')
    {
        $record = $this->getSerializer()->deserialize(
            $content,
            $documentClass,
            $format
        );

        return $record;
    }

    public function validateContent($content, DocumentModel $model)
    {
        $file = '/tmp/hans-'.$model->getEntityClass();
        if (!file_exists($file)) {
            $schema = $this->serializeContent(
                $this->schemaUtils->getModelSchema(null, $model)
            );
            file_put_contents($file, $schema);
        } else {
            $schema = file_get_contents($file);
        }

        return $this->schemaValidator->validate(json_decode($content), json_decode($schema));
    }

    /**
     * Get the serializer
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get the serializer context
     *
     * @return SerializationContext
     */
    public function getSerializerContext()
    {
        return clone $this->serializerContext;
    }

    /**
     * It has been deemed that we search for OPTION routes in order to detect our
     * service routes and then derive the rest from them.
     *
     * @return array An array with option routes
     */
    public function getOptionRoutes()
    {
        $router = $this->router;
        $ret = array_filter(
            $router->getRouteCollection()
                   ->all(),
            function ($route) {
                if (!in_array('OPTIONS', $route->getMethods())) {
                    return false;
                }
                // ignore all schema routes
                if (strpos($route->getPath(), '/schema') === 0) {
                    return false;
                }
                if ($route->getPath() == '/' || $route->getPath() == '/core/version') {
                    return false;
                }

                return is_null($route->getRequirement('id'));
            }
        );

        return $ret;
    }

    /**
     * Based on $baseName, this function returns all routes that match this basename..
     * So if you pass graviton.cont.action; it will return all route names that start with the same.
     * In our routing naming schema, this means all the routes from the same controller.
     *
     * @param string $baseName basename
     *
     * @return array array with matching routes
     */
    public function getRoutesByBasename($baseName)
    {
        $ret = array();
        foreach ($this->router
                      ->getRouteCollection()
                      ->all() as $routeName => $route) {
            if (preg_match('/^' . $baseName . '/', $routeName)) {
                $ret[$routeName] = $route;
            }
        }

        return $ret;
    }

    /**
     * Gets the Model assigned to the RestController
     *
     * @param Route $route Route
     *
     * @return bool|object The model or false
     * @throws \Exception
     */
    public function getModelFromRoute(Route $route)
    {
        $ret = false;
        $controller = $this->getControllerFromRoute($route);

        if ($controller instanceof RestController) {
            $ret = $controller->getModel();
        }

        return $ret;
    }

    /**
     * Gets the controller from a Route
     *
     * @param Route $route Route
     *
     * @return bool|object The controller or false
     */
    public function getControllerFromRoute(Route $route)
    {
        $ret = false;
        $actionParts = explode(':', $route->getDefault('_controller'));

        if (count($actionParts) == 2) {
            $ret = $this->container->get($actionParts[0]);
        }

        return $ret;
    }
}
