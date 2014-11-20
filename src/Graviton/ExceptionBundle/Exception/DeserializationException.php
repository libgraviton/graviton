<?php
namespace Graviton\ExceptionBundle\Exception;

/**
 * Validation exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class DeserializationException extends RestException
{
    /**
     * Constructor
     *
     * @param string $message Error message
     * @param number $code    Error code
     *
     * @return void
     */
    public function __construct($message = "Deserialization Error", $code = 500, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
