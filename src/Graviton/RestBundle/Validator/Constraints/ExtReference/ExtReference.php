<?php
/**
 * ExtReference class file
 */

namespace Graviton\RestBundle\Validator\Constraints\ExtReference;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the extref type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReference extends Constraint
{
    /**
     * @var string
     */
    public $invalidMessage = 'URL "%url%" is not a valid ext reference.';
    /**
     * @var string
     */
    public $notAllowedMessage = 'URL "%url%" is not allowed.';
    /**
     * @var array
     */
    public $allowedCollections;

    /**
     * Returns the name of the class that validates this constraint.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'graviton.rest.validator.extref';
    }
}
