<?php
/**
 * Not found exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Not found exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class NotFoundException extends NotFoundHttpException implements RestExceptionInterface
{
    use RestExceptionTrait;

    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Not Found", $prev = null)
    {
        parent::__construct($message, $prev);
    }
}
