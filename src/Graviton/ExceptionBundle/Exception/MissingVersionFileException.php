<?php
/**
 * MissingVersionFileException class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * MissingVersionFileException class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class MissingVersionFileException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "versions.json not found", $prev = null)
    {
        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $prev);
    }
}
