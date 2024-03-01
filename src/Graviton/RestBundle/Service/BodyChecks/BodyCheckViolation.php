<?php
/**
 * BodyCheckViolation
 */

namespace Graviton\RestBundle\Service\BodyChecks;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class BodyCheckViolation extends \Exception
{

    /**
     * @var string
     */
    public string $propertyPath = '';

    public function __construct(string $message = "", string $propertyPath = '.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->propertyPath = $propertyPath;
    }
}
