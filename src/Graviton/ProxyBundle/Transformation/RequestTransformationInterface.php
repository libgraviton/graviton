<?php
/**
 * RequestTransformation
 */

namespace Graviton\ProxyBundle\Transformation;

use Symfony\Component\HttpFoundation\Request;

/**
 * This class interface should be used by transformers transforming HTTP requests.
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
interface RequestTransformationInterface
{

    /**
     * Transforms a request
     *
     * @param  Request $requestIn  The original request object
     * @param  Request $requestOut The request object to transform
     * @return null|Request The returned Request will be used as $requestOUt for following transformations.
     * If you do not return any request, the same $requestOut instance will be used again.
     */
    public function transformRequest(Request $requestIn, Request $requestOut);
}
