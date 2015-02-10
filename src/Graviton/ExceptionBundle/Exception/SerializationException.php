<?php
/**
 * Serialization exception class
 *
 * PHP Version 5
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Serialization exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
final class SerializationException extends RestException
{
    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     *
     * @return void
     */
    public function __construct($message = "Serialization Error", $prev = null)
    {
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR, $prev);
    }
}
