<?php
/**
 * Deserialization exception class
 */

namespace Graviton\RestBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Deserialization exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class DeserializationException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Deserialization Error", $prev = null)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            $message,
            $prev
        );
    }
}
