<?php
/**
 * HTTP Request & Response Body mapper
 */

namespace Graviton\ProxyBundle\Transformation;

use Graviton\ProxyBundle\Service\MappingTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class transforms the body of HTTP requests and responses.
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class BodyMapping implements ResponseTransformationInterface, RequestTransformationInterface
{

    /**
     * @var array
     */
    private $mapping = [];

    /**
     * @var MappingTransformer
     */
    private $transformer;

    /**
     * Constructor
     *
     * @param MappingTransformer $transformer The MappingTransformer to be used for the transformations
     */
    public function __construct(MappingTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Transforms a response
     *
     * @param Response $responseIn  The original response object
     * @param Response $responseOut The response object to transform
     *
     * @return void
     */
    public function transformResponse(Response $responseIn, Response $responseOut)
    {
        $responseOut->setContent(
            json_encode($this->transformer->transform($responseIn->getContent(), $this->mapping))
        );
    }

    /**
     * Transforms a request
     *
     * @param  Request $requestIn  The original request object
     * @param  Request $requestOut The request object to transform
     * @return Request The transformed request
     */
    public function transformRequest(Request $requestIn, Request $requestOut)
    {
        $requestOut = Request::create(
            $requestOut->getUri(),
            $requestOut->getMethod(),
            [],
            [],
            [],
            [],
            json_encode(
                $this->transformer->transform(json_decode($requestIn->getContent()), $this->mapping)
            )
        );
        return $requestOut;
    }

    /**
     * Sets the mapping
     *
     * Structure: $transformedPropertyPath => $originalPropertyPath
     *
     * @see http://symfony.com/doc/current/components/property_access/introduction.html
     *
     * @param array $mapping The mapping
     *
     * @return void
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }
}
