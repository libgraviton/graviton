<?php
namespace Graviton\RestBundle\Action;

/**
 * Delete Action
 *
 * Return false. No Link header needed for delete action
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class DeleteAction extends AbstractAction
{
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\AbstractAction::getRefLink()
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        return null;
    }
}
