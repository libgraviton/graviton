<?php
namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check for "id" presence in POST request.
 * It's an own class to present a different message to the user.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class PostId extends Constraint
{

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'Can not be given on a POST request. Do a PUT request instead to update an existing record.';
}
