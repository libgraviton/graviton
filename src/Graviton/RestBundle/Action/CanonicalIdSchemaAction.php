<?php
namespace Graviton\RestBundle\Action;

/**
 * Id schema Action
 *
 * Return schema url
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class CanonicalIdSchemaAction extends AbstractAction
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
        return $this->generateUrl($router, self::ACTION_CANONICAL_ID_SCHEMA, array(), $absolute);
    }
}
