<?php
/**
 * MalformedInput exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * MalformedInput exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class MalformedInputException extends RestException
{

    private $errorTypes = array(
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or modes mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );

    /**
     * Constructor
     *
     * @param string     $message   Error message
     * @param \Exception $prev      Previous Exception
     * @param string     $jsonError json error type
     */
    public function __construct($message = "Malformed input", $prev = null, $jsonError = null)
    {
        if (!is_null($jsonError) && isset($this->errorTypes[$jsonError])) {
            $message .= ' - '.$this->errorTypes[$jsonError];
        }

        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            $message,
            $prev
        );
    }
}
