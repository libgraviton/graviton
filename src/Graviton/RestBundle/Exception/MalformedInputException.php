<?php
/**
 * MalformedInput exception class
 */

namespace Graviton\RestBundle\Exception;

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

    /**
     * Constructor
     *
     * @param string     $message   Error message
     * @param \Exception $prev      Previous Exception
     * @param ?string    $jsonError json error type
     */
    public function __construct($message = "Malformed input", $prev = null, ?string $jsonError = null)
    {
        if (!is_null($jsonError)) {
            $message .= ' - '.$jsonError;
        }

        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            $message,
            $prev
        );
    }
}
