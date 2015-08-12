<?php
/**
 * InvalidJsonPatchException exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * InvalidJsonPatchException exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class InvalidJsonPatchException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Invalid JSON Patch", $prev = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $prev);
    }
}
