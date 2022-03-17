<?php
/**
 * basic version model
 */

namespace Graviton\CoreBundle\Model;

use Graviton\RestBundle\Model\DocumentModel;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Version extends DocumentModel
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct(__DIR__.'/../Resources/config/schema/Version.json');
    }
}
