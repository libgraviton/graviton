<?php
/**
 * TransformationHandler
 */

namespace Graviton\ProxyBundle\Service;

use Graviton\ProxyBundle\Transformation\RequestTransformationInterface;
use Graviton\ProxyBundle\Transformation\ResponseTransformationInterface;
use Graviton\ProxyBundle\Transformation\SchemaTransformationInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class handles all transformations for all proxied API's and their endpoints.
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class TransformationHandler
{
    /**
     * @var array
     */
    protected $requestTransformations = [];

    /**
     * @var array
     */
    protected $responseTransformations = [];

    /**
     * @var array
     */
    protected $schemaTransformations = [];

    /**
     * Applies all transformations defined for the given API and endpoint on a request.
     *
     * @param  string  $api        The API name
     * @param  string  $endpoint   The endpoint
     * @param  Request $requestIn  The original request object.
     * @param  Request $requestOut The request object to use for transformations
     * @return Request The transformed request
     * @throws TransformationException
     */
    public function transformRequest($api, $endpoint, Request $requestIn, Request $requestOut)
    {
        $transformations = $this->getRequestTransformations($api, $endpoint);

        if (!empty($transformations)) {
            foreach ($transformations as $transformation) {
                $transformedRequest = $transformation->transformRequest($requestIn, $requestOut);
                $requestOut = $transformedRequest instanceof Request ? $transformedRequest : $requestOut;
            }

            return $requestOut;
        }

        // TODO [taafeba2]: add logging
        return $requestOut;
    }

    /**
     * Applies all transformations defined for the given API and endpoint on a response.
     *
     * @param  string   $api         The API name
     * @param  string   $endpoint    The endpoint
     * @param  Response $responseIn  The original response object
     * @param  Response $responseOut The response object to use for transformations
     * @return Response The transformed response
     */
    public function transformResponse($api, $endpoint, Response $responseIn, Response $responseOut)
    {
        $transformations = $this->getResponseTransformations($api, $endpoint);
        foreach ($transformations as $transformation) {
            $transformedRequest = $transformation->transformResponse($responseIn, $responseOut);
            $responseOut = $transformedRequest instanceof Response ? $transformedRequest : $responseOut;
        }
        return $responseOut;
    }

    /**
     * Applies all transformations defined for the given API and endpoint on a schema.
     *
     * @param  string    $api       The API name
     * @param  string    $endpoint  The endpoint
     * @param  \stdClass $schemaIn  The original schema object
     * @param  \stdClass $schemaOut The schema object to use for transformations
     * @return \stdClass The transformed schema
     */
    public function transformSchema($api, $endpoint, \stdClass $schemaIn, \stdClass $schemaOut)
    {
        $transformations = $this->getSchemaTransformations($api, $endpoint);
        foreach ($transformations as $transformation) {
            $transformedSchema = $transformation->transformSchema($schemaIn, $schemaOut);
            $schemaOut = $transformedSchema instanceof \stdClass ? $transformedSchema : $schemaOut;
        }
        return $schemaOut;
    }

    /**
     * Returns the request transformations registered for a given API and endpoint.
     *
     * @param  string $api      The API name
     * @param  string $endpoint The endpoint
     * @return array The transformations
     */
    public function getRequestTransformations($api, $endpoint)
    {
        if (isset($this->requestTransformations[$api])) {
            $patterns = array_keys($this->requestTransformations[$api]);

            foreach ($patterns as $pattern) {
                preg_match($pattern, $endpoint, $matches);

                if (!empty($matches[1])) {
                    return $this->requestTransformations[$api][$pattern];
                }
            }
        }

        return [];
    }

    /**
     * Returns the transformations registered for a given API and endpoint.
     *
     * @param  string $api      The API name
     * @param  string $endpoint The endpoint
     * @return array The transformations
     */
    public function getResponseTransformations($api, $endpoint)
    {
        return isset($this->responseTransformations[$api][$endpoint]) ?
            $this->responseTransformations[$api][$endpoint] : [];
    }

    /**
     * Returns the transformations registered for a given API and endpoint.
     *
     * @param  string $api      The API name
     * @param  string $endpoint The endpoint
     * @return array The transformations
     */
    public function getSchemaTransformations($api, $endpoint)
    {
        return isset($this->schemaTransformations[$api][$endpoint]) ?
            $this->schemaTransformations[$api][$endpoint] : [];
    }

    /**
     * Adds a transformation to a given API and endpoint.
     *
     * @param  string                         $api            The API name
     * @param  string                         $endpoint       The API endpoint
     * @param  RequestTransformationInterface $transformation The transformation
     * @return int The position of the added transformation within the transformation array.
     */
    public function addRequestTransformation($api, $endpoint, RequestTransformationInterface $transformation)
    {
        $this->requestTransformations[$api][$endpoint][] = $transformation;
        return count($this->requestTransformations[$api][$endpoint]) - 1;
    }

    /**
     * Adds a response transformation to a given API and endpoint.
     *
     * @param  string                          $api            The API name
     * @param  string                          $endpoint       The API endpoint
     * @param  ResponseTransformationInterface $transformation The transformation
     * @return int The position of the added transformation within the transformation array.
     */
    public function addResponseTransformation($api, $endpoint, ResponseTransformationInterface $transformation)
    {
        $this->responseTransformations[$api][$endpoint][] = $transformation;
        return count($this->responseTransformations[$api][$endpoint]) - 1;
    }

    /**
     * Adds a schema transformation to a given API and endpoint.
     *
     * @param  string                        $api            The API name
     * @param  string                        $endpoint       The API endpoint
     * @param  SchemaTransformationInterface $transformation The transformation
     * @return int The position of the added transformation within the transformation array.
     */
    public function addSchemaTransformation($api, $endpoint, SchemaTransformationInterface $transformation)
    {
        $this->schemaTransformations[$api][$endpoint][] = $transformation;
        return count($this->schemaTransformations[$api][$endpoint]) - 1;
    }
}
