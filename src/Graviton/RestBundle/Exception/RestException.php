<?php
/**
 * Base rest exception class
 */

namespace Graviton\RestBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Base rest exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class RestException extends HttpException
{
}
