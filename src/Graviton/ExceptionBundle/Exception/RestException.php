<?php
/**
 * Base rest exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Base rest exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class RestException extends HttpException implements RestExceptionInterface
{
    use RestExceptionTrait;
}
