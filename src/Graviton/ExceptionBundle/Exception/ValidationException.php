<?php
/**
 * Validation exception class
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

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validation exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
     *
     * @return void
     */
    public function __construct($message = "Validation Failed", $prev = null)
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST, $prev);
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
