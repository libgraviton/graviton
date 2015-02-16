<?php
/**
 * Proxy RestController.
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\RestBundle\Controller\RestController;

/**
 * Proxy RestController.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RestControllerProxy extends RestController
{
    /**
     * validate a single record
     *
     * @param object $record record to validate
     *
     * @return void
     */
    public function validateRecord($record)
    {
        parent::validateRecord($record);
    }
}
