<?php
/**
 * Base rest exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base rest exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
trait RestExceptionTrait
{
    /**
     * Response object
     *
     * @var Response
     */
    private $response = false;

    /**
     * Set the response object (optional)
     *
     * @param Response $response Response object
     *
     * @return RestException $this This
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response object
     *
     * @return Response $response Response object
     */
    public function getResponse()
    {
        return $this->response;
    }
}
