<?php
/**
 * Deserialization exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Deserialization exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR, $prev);
    }
}
