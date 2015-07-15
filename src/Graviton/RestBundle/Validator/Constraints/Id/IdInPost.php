<?php
/**
 * Constraint for the ID in payload in PUT request
 */

namespace Graviton\RestBundle\Validator\Constraints\Id;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the ID in payload in PUT requests
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class IdInPost extends Constraint
{

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'Can not be given on a POST request. Do a PUT request instead to update an existing record.';
}
