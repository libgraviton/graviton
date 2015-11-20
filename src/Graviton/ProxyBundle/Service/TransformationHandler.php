<?php
/**
 * TransformationHandler
 */

namespace Graviton\ProxyBundle\Service;

use Graviton\ProxyBundle\Transformation\RequestTransformation;
use Graviton\ProxyBundle\Transformation\ResponseTransformation;
use Graviton\ProxyBundle\Transformation\SchemaTransformation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class handles all transformations for all proxied API's and their endpoints.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TransformationHandler
{

    protected $transformations = [];

    /**
     * Applies all transformations defined for the given API and endpoint on a request.
     *
     * @param $api The API name
     * @param $endpoint The endpoint
     * @param Request $requestIn The original request object.
     * @param Request $requestOut The request object to use for transformations
     * @return Request The transformed request
     */
    public function transformRequest($api, $endpoint, Request $requestIn, Request $requestOut)
    {
        $transformations = $this->getTransformations($api, $endpoint);
        foreach ($transformations as $transformation) {
            if ($transformation instanceof RequestTransformation) {
                $transformedRequest = $transformation->transformRequest($requestIn, $requestOut);
                $requestOut = $transformedRequest instanceof Request ? $transformedRequest : $requestOut;
            }
        }
        return $requestOut;
    }

    /**
     * Applies all transformations defined for the given API and endpoint on a response.
     *
     * @param $api The API name
     * @param $endpoint The endpoint
     * @param Response $responseIn The original response object
     * @param Response $responseOut The response object to use for transformations
     * @return Response The transformed response
     */
    public function transformResponse($api, $endpoint, Response $responseIn, Response $responseOut)
    {
        $transformations = $this->getTransformations($api, $endpoint);
        foreach ($transformations as $transformation) {
            if ($transformation instanceof ResponseTransformation) {
                $transformedRequest = $transformation->transformResponse($responseIn, $responseOut);
                $responseOut = $transformedRequest instanceof Response ? $transformedRequest : $responseOut;
            }
        }
        return $responseOut;
    }

    /**
     * Applies all transformations defined for the given API and endpoint on a schema.
     *
     * @param $api The API name
     * @param $endpoint The endpoint
     * @param \stdClass $schemaIn The original schema object
     * @param \stdClass $schemaOut The schema object to use for transformations
     * @return \stdClass The transformed schema
     */
    public function transformSchema($api, $endpoint, \stdClass $schemaIn, \stdClass $schemaOut)
    {
        $transformations = $this->getTransformations($api, $endpoint);
        foreach ($transformations as $transformation) {
            if ($transformation instanceof SchemaTransformation) {
                $transformedSchema = $transformation->transformSchema($schemaIn, $schemaOut);
                $schemaOut = $transformedSchema instanceof \stdClass ? $transformedSchema : $schemaOut;
            }
        }
        return $schemaOut;
    }

    /**
     * Returns the transformations registered for a given API and endpoint.
     *
     * @param $api The API name
     * @param $endpoint The endpoint
     * @return array The transformations
     */
    public function getTransformations($api, $endpoint)
    {
        return isset($this->transformations[$api][$endpoint]) ? $this->transformations[$api][$endpoint] : [];
    }

    /**
     * Adds a transformation to a given API and endpoint.
     *
     * @param $api The API name
     * @param $endpoint The API endpoint
     * @param $transformation The transformation
     * @return int The position of the added transformation within the transformation array.
     */
    public function addTransformation($api, $endpoint, $transformation)
    {
        $this->transformations[$api][$endpoint][] = $transformation;
        return count($this->transformations[$api][$endpoint]) - 1;
    }

}