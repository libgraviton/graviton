<?php
/**
 * ResponseTransformation
 */

namespace Graviton\ProxyBundle\Transformation;

use Symfony\Component\HttpFoundation\Response;

/**
 * This class interface should be used by transformers transforming HTTP responses.
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
interface ResponseTransformationInterface
{

    /**
     * Transforms a response
     *
     * @param  Response $responseIn  The original response object
     * @param  Response $responseOut The response object to transform
     * @return null|Response The returned Response will be used as $responseOut for following transformations.
     * If you do not return any response, the same $responseOut instance will be used again.
     */
    public function transformResponse(Response $responseIn, Response $responseOut);
}
