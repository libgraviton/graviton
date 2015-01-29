<?php

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\RestBundle\Controller\RestController;

/**
 * Proxy ResetController.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Bastian Feder <lapistano@bastian-feder.de>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestControllerProxy extends RestController
{
    public function validateRecord($record)
    {
        parent::validateRecord($record);
    }
}
