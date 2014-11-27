<?php
namespace Graviton\RestBundle\Action;

/**
 * Schema Action
 * 
 * First Question:
 * What is the benefit of this? When calling "/schema/<<service>>/collection", the
 * only additional information compared to "/schema/<<service>>/item"
 * is that a collection is an array of object (i knew this before and people working with
 * rest services should too..)
 * 
 * Maybe i'm wrong but found no additional information about the idea behind this...
 * 
 * Second Question: 
 * Why is this necessary if the options and idOptions actions do the same?
 * Sending a HTTP OPTIONS request to the given uri (/<<service>> should be enough...
 * 
 * Nevertheless, i implement it because there are routes defined for this actions...
 *
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class CanonicalSchemaAction extends AbstractAction
{
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\AbstractAction::getRefLink()
     */
    public function getRefLinkUrl($router, $absolute = false)
    {
        $route = $this->getRoute(self::ACTION_CANONICAL_SCHEMA);

        return $router->generate($route, array(), $absolute);
    }
}
