<?php
/**
 * service for RESTy stuff
 */

namespace Graviton\RestBundle\Service;

use Graviton\ExceptionBundle\Exception\DeserializationException;
use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\ExceptionBundle\Exception\NoInputException;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Graviton\JsonSchemaBundle\Exception\ValidationException;
use Graviton\JsonSchemaBundle\Exception\ValidationExceptionError;
use Graviton\JsonSchemaBundle\Validator\Validator;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\SchemaBundle\SchemaUtils;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use JMS\Serializer\Serializer;
use Graviton\RestBundle\Controller\RestController;

/**
 * A service (meaning symfony service) providing some convenience stuff when dealing with our RestController
 * based services (meaning rest services).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class RestUtils implements RestUtilsInterface
{

    /**
     * @var Serializer
     */
    private $serializer;

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
     * @var CacheItemPoolInterface
     */
    private $cacheProvider;

    /**
     * @param Router                 $router          router
     * @param Serializer             $serializer      serializer
     * @param LoggerInterface        $logger          PSR logger (e.g. Monolog)
     * @param SchemaUtils            $schemaUtils     schema utils
     * @param Validator              $schemaValidator schema validator
     * @param CacheItemPoolInterface $cacheProvider   Cache service
     */
    public function __construct(
        Router $router,
        Serializer $serializer,
        LoggerInterface $logger,
        SchemaUtils $schemaUtils,
        Validator $schemaValidator,
        CacheItemPoolInterface $cacheProvider
    ) {
        $this->serializer = $serializer;
        $this->router = $router;
        $this->logger = $logger;
        $this->schemaUtils = $schemaUtils;
        $this->schemaValidator = $schemaValidator;
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * Builds a map of baseroutes (controllers) to its relevant route to the actions.
     * ignores schema stuff.
     *
     * @return array grouped array of basenames and actions..
     */
    public function getServiceRoutingMap()
    {
        $ret = [];
        $optionRoutes = $this->getOptionRoutes();

        foreach ($optionRoutes as $routeName => $optionRoute) {
            // get base name from options action
            $routeParts = explode('.', $routeName);
            if (count($routeParts) < 3) {
                continue;
            }
            array_pop($routeParts); // get rid of last part
            $baseName = implode('.', $routeParts);

            // get routes from same controller
            foreach ($this->getRoutesByBasename($baseName) as $routeName => $route) {
                // don't put schema stuff
                if (!str_contains('schema', strtolower($routeName))) {
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
                $format
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

    /**
     * Validates content with the given schema, returning an array of errors.
     * If all is good, you will receive an empty array.
     *
     * @param object        $content \stdClass of the request content
     * @param DocumentModel $model   the model to check the schema for
     *
     * @return \Graviton\JsonSchemaBundle\Exception\ValidationExceptionError[]
     * @throws \Exception
     */
    public function validateContent($content, DocumentModel $model)
    {
        if (is_string($content)) {
            $content = json_decode($content);
        }

        return $this->schemaValidator->validate(
            $content,
            $this->schemaUtils->getModelSchema(null, $model, true, true, true, $content)
        );
    }

    /**
     * validate raw json input
     *
     * @param Request       $request  request
     * @param Response      $response response
     * @param DocumentModel $model    model
     * @param string        $content  Alternative request content.
     *
     * @return void
     */
    public function checkJsonRequest(Request $request, Response $response, DocumentModel $model, $content = '')
    {
        if (empty($content)) {
            $content = $request->getContent();
        }

        if (is_resource($content)) {
            throw new BadRequestHttpException('unexpected resource in validation');
        }

        // is request body empty
        if ($content === '') {
            throw new NoInputException();
        }

        $input = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new MalformedInputException(jsonError: json_last_error_msg());
        }
        if (!is_array($input)) {
            throw new MalformedInputException('JSON request body must be an object');
        }

        if ($request->getMethod() == 'PUT' && array_key_exists('id', $input)) {
            // we need to check for id mismatches....
            if ($request->attributes->get('id') != $input['id']) {
                throw new MalformedInputException('Record ID in your payload must be the same');
            }
        }

        if ($request->getMethod() == 'POST' &&
            array_key_exists('id', $input) &&
            !$model->isIdInPostAllowed()
        ) {
            throw new MalformedInputException(
                '"id" can not be given on a POST request. Do a PUT request instead to update an existing record.'
            );
        }
    }

    /**
     * Validate JSON patch for any object
     *
     * @param array $jsonPatch json patch as array
     *
     * @throws InvalidJsonPatchException
     * @return void
     */
    public function checkJsonPatchRequest(array $jsonPatch)
    {
        foreach ($jsonPatch as $operation) {
            if (!is_array($operation)) {
                throw new InvalidJsonPatchException('Patch request should be an array of operations.');
            }
            if (array_key_exists('path', $operation) && trim($operation['path']) == '/id') {
                throw new InvalidJsonPatchException('Change/remove of ID not allowed');
            }
        }
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
     * It has been deemed that we search for OPTION routes in order to detect our
     * service routes and then derive the rest from them.
     *
     * @return array An array with option routes
     */
    public function getOptionRoutes()
    {
        $cacheItem = $this->cacheProvider->getItem('cached_restutils_route_options');

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $ret = array_filter(
            $this->router->getRouteCollection()->all(),
            function ($route) {
                if (!in_array('OPTIONS', $route->getMethods())) {
                    return false;
                }
                // ignore all schema routes
                if (str_starts_with($route->getPath(), '/schema')) {
                    return false;
                }
                if ($route->getPath() == '/' || $route->getPath() == '/core/version') {
                    return false;
                }

                return is_null($route->getRequirement('id'));
            }
        );

        $cacheItem->set($ret);
        $this->cacheProvider->save($cacheItem);

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
        $cacheId = 'cached_restutils_route_'.$baseName;
        $cacheItem = $this->cacheProvider->getItem($cacheId);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $ret = [];
        $collections = $this->router->getRouteCollection()->all();
        foreach ($collections as $routeName => $route) {
            if (preg_match('/^' . $baseName . '/', $routeName)) {
                $ret[$routeName] = $route;
            }
        }

        $cacheItem->set($ret);
        $this->cacheProvider->save($cacheItem);

        return $ret;
    }

    /**
     * @param Request $request request
     * @return string
     */
    public function getRouteName(Request $request)
    {
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4) . 'get';
        }

        return $routeName;
    }

    /**
     * Serialize the given record and throw an exception if something went wrong
     *
     * @param object|object[] $result Record(s)
     *
     * @throws \Graviton\ExceptionBundle\Exception\SerializationException
     *
     * @return string $content Json content
     */
    public function serialize($result)
    {
        try {
            // array is serialized as an object {"0":{...},"1":{...},...} when data contains an empty objects
            // we serialize each item because we can assume this bug affects only root array element
            if (is_array($result) && array_keys($result) === range(0, count($result) - 1)) {
                $result = array_map(
                    function ($item) {
                        return $this->serializeContent($item);
                    },
                    $result
                );

                return '['.implode(',', array_filter($result)).']';
            }

            return $this->serializeContent($result);
        } catch (\Exception $e) {
            throw new SerializationException(prev: $e);
        }
    }

    /**
     * Deserialize the given content throw an exception if something went wrong
     *
     * @param string $content       Request content
     * @param string $documentClass Document class
     *
     * @throws DeserializationException
     *
     * @return object $record Document
     */
    public function deserialize($content, $documentClass)
    {
        try {
            $record = $this->deserializeContent(
                $content,
                $documentClass
            );
        } catch (\Exception $e) {
            throw new DeserializationException(prev: $e);
        }

        return $record;
    }

    /**
     * Validates the current request on schema violations. If there are errors,
     * the exception is thrown. If not, the deserialized record is returned.
     *
     * @param object|string $content \stdClass of the request content
     * @param DocumentModel $model   the model to check the schema for
     *
     * @return ValidationExceptionError|Object
     * @throws \Exception
     */
    public function validateRequest($content, DocumentModel $model)
    {
        $errors = $this->validateContent($content, $model);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        return $this->deserialize($content, $model->getEntityClass());
    }
}
