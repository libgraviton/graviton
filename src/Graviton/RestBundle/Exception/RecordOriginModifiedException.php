<?php
/**
 * Record origin modified exception class
 */

namespace Graviton\RestBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Record origin modified exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RecordOriginModifiedException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct(string $message, $prev = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $prev);
    }
}
