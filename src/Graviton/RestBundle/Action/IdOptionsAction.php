<?php
namespace Graviton\RestBundle\Action;

/**
 * Schema Action
 *
 * Return schema url
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class IdOptionsAction extends AbstractAction
{
    /**
     * (non-PHPdoc)
     *
     * @param RouterInterface $router   Router instance
     * @param bool            $absolute Absolute path
     *
     * @see \Graviton\RestBundle\Action\AbstractAction::getRefLink()
     *
     * @return string $url Url
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        $id = $this->getRequest()->get('id');
        $url = $this->generateUrl($router, self::ACTION_ID_OPTIONS, array('id' => $id), $absolute);

        return $url;
    }
}
