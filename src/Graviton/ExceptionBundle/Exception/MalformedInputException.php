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
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class MalformedInputException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Malformed input", $prev = null)
    {
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR, $prev);
    }
}
