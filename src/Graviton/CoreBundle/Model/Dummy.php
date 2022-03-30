<?php
/**
 * dummy model
 */

namespace Graviton\CoreBundle\Model;

use Graviton\RestBundle\Model\DocumentModel;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Dummy extends DocumentModel
{
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct(__DIR__.'/../Resources/config/schema/Dummy.json');
    }
}
