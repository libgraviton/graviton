<?php
/**
 * RqlOperatorNotAllowedException class file
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * RqlOperatorNotAllowed exception
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class RqlOperatorNotAllowedException extends RestException
{

    /**
     * Constructor
     *
     * @param string $operator RQL operator
     */
    public function __construct($operator)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            sprintf('RQL operator "%s" is not allowed for this request', $operator)
        );
    }
}
