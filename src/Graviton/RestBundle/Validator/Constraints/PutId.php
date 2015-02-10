<?php
/**
 * PUT id in request URL must be the same as in payload.
 *
 * PHP Version 5
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * PUT id in request URL must be the same as in payload.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PutId extends Constraint
{

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'Record ID in your payload must be the same as in your request URL.';

    /**
     * The record id to update
     *
     * @var mixed
     */
    protected $updateId;

    /**
     * Set the update id
     *
     * @param mixed $updateId update id
     *
     * @return void
     */
    public function setUpdateId($updateId)
    {
        $this->updateId = $updateId;
    }

    /**
     * Get the update id
     *
     * @return mixed
     */
    public function getUpdateId()
    {
        return $this->updateId;
    }
}
