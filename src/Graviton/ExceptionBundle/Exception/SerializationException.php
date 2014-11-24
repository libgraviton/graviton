<?php
namespace Graviton\ExceptionBundle\Exception;

/**
 * Serialization exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SerializationException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param number     $code    Error code
     * @param /Exception $prev    Previous Exception
     *
     * @return void
     */
    public function __construct($message = "Serialization Error", $code = 500, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
