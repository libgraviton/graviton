<?php
namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Base rest exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
abstract class RestException extends \Exception
{
    /**
     * Response object
     *
     * @var Symfony\Component\HttpFoundation\Response
     */
    private $response = false;

    /**
     * Set the response object (optional)
     *
     * @param Response $response Response object
     *
     * @return \Graviton\ExceptionBundle\Exception\ValidationException $this This
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
