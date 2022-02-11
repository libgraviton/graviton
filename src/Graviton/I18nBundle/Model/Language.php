<?php
/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 */

namespace Graviton\I18nBundle\Model;

use Graviton\RestBundle\Model\DocumentModel;

/**
 * Model based on Graviton\RestBundle\Model\DocumentModel.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Language extends DocumentModel
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct(__DIR__.'/../Resources/config/schema/Language.json');
    }
}
