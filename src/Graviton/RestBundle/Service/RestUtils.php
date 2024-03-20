<?php
/**
 * service for RESTy stuff
 */

namespace Graviton\RestBundle\Service;

use Graviton\RestBundle\Exception\DeserializationException;
use Graviton\RestBundle\Exception\InvalidJsonPatchException;
use Graviton\RestBundle\Exception\MalformedInputException;
use Graviton\RestBundle\Exception\SerializationException;
use Graviton\RestBundle\Model\DocumentModel;
use JMS\Serializer\Serializer;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rs\Json\Pointer;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A service (meaning symfony service) providing some convenience stuff when dealing with our RestController
 * based services (meaning rest services).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class RestUtils
{

    /**
     * @param Serializer                  $serializer         serializer
     * @param BodyChecker                 $bodyChecker        body checker
     * @param LoggerInterface             $logger             logger
     * @param CacheItemPoolInterface      $cacheProvider      cache provider
     * @param HttpMessageFactoryInterface $httpMessageFactory psr factory
     */
    public function __construct(
        private Serializer $serializer,
        private BodyChecker $bodyChecker,
        private LoggerInterface $logger,
        private CacheItemPoolInterface $cacheProvider,
        private HttpMessageFactoryInterface $httpMessageFactory
    ) {
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
     * @return object|array|integer|double|string|boolean
     * @throws \Exception
     *
     */
    public function deserializeContent($content, string $documentClass, string $format = 'json')
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
     * @param ServerRequestInterface $request        request
     * @param Response               $response       response
     * @param DocumentModel          $model          the model to check the schema for
     * @param bool                   $skipBodyChecks should we skip body checks?
     *
     * @return ServerRequestInterface request
     *
     * @throws \Exception|\Throwable
     */
    public function validatePsrRequest(
        ServerRequestInterface $request,
        Response $response,
        DocumentModel $model,
        bool $skipBodyChecks = false
    ) : ServerRequestInterface {

        // slash missing at the end of POST requests
        if ($request->getMethod() == 'POST' && !str_ends_with($request->getUri()->getPath(), '/')) {
            $newUri = $request->getUri()->withPath(
                $request->getUri()->getPath() . '/'
            );
            $request = $request->withUri($newUri);
        }

        // first, body checks!
        if (!$skipBodyChecks) {
            $request = $this->validateBodyChecks($request, $response, $model);
        }

        $validator = (new ValidatorBuilder())
            ->setCache($this->cacheProvider)
            ->fromJsonFile($model->getSchemaPath())
            ->getServerRequestValidator();

        $validator->validate($request);

        return $request;
    }

    /**
     * validates the passed request and converts it to a PSR request - throws an Exception if something is wrong
     *
     * @param Request       $request        request
     * @param Response      $response       response
     * @param DocumentModel $model          model
     * @param bool          $skipBodyChecks should the body be checked?
     * @return ServerRequestInterface
     * @throws \Exception
     */
    public function validateRequest(
        Request $request,
        Response $response,
        DocumentModel $model,
        bool $skipBodyChecks = false
    ) : ServerRequestInterface {
        return $this->validatePsrRequest(
            $this->httpMessageFactory->createRequest($request),
            $response,
            $model,
            $skipBodyChecks
        );
    }

    /**
     * performs body checks. these are checks that cannot be done by the openapi validator library - they
     * mostly rely on the current database object.
     *
     * @param ServerRequestInterface $request  request
     * @param Response               $response response
     * @param DocumentModel          $model    model
     *
     * @return void
     *
     * @throws \Throwable
     */
    private function validateBodyChecks(
        ServerRequestInterface $request,
        Response $response,
        DocumentModel $model
    ) : ServerRequestInterface {
        $id = $this->getTargetIdFromRequest($request);
        return $this->bodyChecker->checkRequest(
            $request,
            $response,
            $model,
            $id
        );
    }

    /**
     * determines which record id the request targets, if any - or it there is a mismatch
     *
     * @param ServerRequestInterface $request request
     *
     * @return string|null id or null
     */
    public function getTargetIdFromRequest(ServerRequestInterface $request) : ?string
    {
        $id = $request->getAttribute('id');

        // no json request?
        $contentType = $request->getHeaderLine('content-type');
        if (!empty($contentType) && !str_contains($contentType, 'json')) {
            return $id;
        }

        // in body?
        $bodyId = null;
        try {
            $body = new Pointer((string) $request->getBody());
            $bodyId = $body->get('/id');
        } catch (\Throwable $t) {
            // it's ok.
        }

        if (!empty($id) && !empty($bodyId) && $id != $bodyId) {
            // collision!
            throw new MalformedInputException('Record ID in your payload must be the same');
        }

        return !empty($id) ? $id : $bodyId;
    }

    /**
     * returns the deserialized entity from the request
     *
     * @param ServerRequestInterface $request request
     * @param DocumentModel          $model   model
     *
     * @return object entity
     */
    public function getEntityFromRequest(ServerRequestInterface $request, DocumentModel $model) : object
    {
        return $this->deserialize($request->getBody(), $model->getEntityClass());
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
     * Serialize the given record and throw an exception if something went wrong
     *
     * @param object|object[] $result Record(s)
     *
     * @return string $content Json content
     * @throws \Graviton\RestBundle\Exception\SerializationException
     *
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
}
