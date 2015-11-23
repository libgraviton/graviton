<?php
/**
 * XDynamicKeyException
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * XDynamicKey exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class XDynamicKeyException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "x-dynamic-key ref-field could not be resolved", $prev = null)
    {

        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $prev);
    }
}
