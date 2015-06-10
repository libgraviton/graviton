<?php
/**
 * Validation exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validation exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class ValidationException extends RestException
{
    /**
     * Violations
     *
     * @var \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private $violations;

    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param \Exception $prev    Previous Exception
     */
    public function __construct($message = "Validation Failed", $prev = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $prev);
    }

    /**
     * Set violations
     *
     * @param \Symfony\Component\Validator\ConstraintViolationList $violations Violation list
     *
     * @return \Graviton\ExceptionBundle\Exception\ValidationException $this This
     */
    public function setViolations(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;

        return $this;
    }

    /**
     * Get violation list
     *
     * @return ConstraintViolationList $violations violations
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
