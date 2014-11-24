<?php
namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Validation exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestException extends \Exception
{
    /**
     * Response object
     *
     * @var Symfony\Component\HttpFoundation\Response
     */
    private $response = false;

    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param number     $code    Error code
     * @param /Exception $prev    Previous Exception
     *
     * @return void
     */
    public function __construct($message, $code = 0, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }

    /**
     * Set the response object (optional)
     *
     * @param \Symfony\Component\HttpFoundation\Response $response Response object
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
     * @return \Symfony\Component\HttpFoundation\Response $response Response object
     */
    public function getResponse()
    {
        return $this->response;
    }
}
