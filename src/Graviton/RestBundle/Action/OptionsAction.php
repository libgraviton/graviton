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
class OptionsAction extends AbstractAction
{
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\AbstractAction::getRefLink()
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        $route = $this->getRoute(self::ACTION_SCHEMA);

        return $router->generate($route, array(), $absolute);
    }
}
