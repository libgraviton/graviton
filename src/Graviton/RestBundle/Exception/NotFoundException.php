<?php
/**
 * Not found exception class
 */

namespace Graviton\RestBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Not found exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class NotFoundException extends RestException
{

    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Not Found", $prev = null)
    {
        parent::__construct(Response::HTTP_NOT_FOUND, $message, $prev);
    }
}
