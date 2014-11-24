<?php
namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validation exception class
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ValidationException extends RestException
{
    /**
     * Violations
     *
     * @var Symfony\Component\Validator\ConstraintViolationList
     */
    private $violations;

    /**
     * Constructor
     *
     * @param string     $message Error message
     * @param number     $code    Error code
     * @param /Exception $prev    Previous Exception
     *
     * @return void
     */
    public function __construct($message = "Validation Failed", $code = 400, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }

    /**
     * Set violations
     *
     * @param Symfony\Component\Validator\ConstraintViolationList $violations Violation list
     *
     * @return \Graviton\ExceptionBundle\Exception\ValidationException $this This
     */
    public function setViolations(ConstraintViolationList $violations)
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
