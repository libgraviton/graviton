<?php
/**
 * MalformedInput exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * MalformedInput exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class MalformedInputException extends BadRequestHttpException implements RestExceptionInterface
{
    use RestExceptionTrait;

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
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Malformed input", $prev = null)
    {
        parent::__construct($message, $prev, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Sets the specific json error type to make consistent error reporting (and thus testing) possible
     *
     * @param int $error Error constant from json_last_error
     *
     * @return void
     */
    public function setErrorType($error)
    {
        if (isset($this->errorTypes[$error])) {
            $this->message = trim($this->errorTypes[$error].': '.$this->message);
        }
    }
}
