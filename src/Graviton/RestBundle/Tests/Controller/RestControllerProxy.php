<?php

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\RestBundle\Controller\RestController;

/**
 * Proxy ResetController.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class RestControllerProxy extends RestController
{
    public function validateRecord($record)
    {
        parent::validateRecord($record);
    }
}
