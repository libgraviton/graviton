<?php

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;


/**
 * Class HeaderFieldStrategy
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HeaderFieldStrategy extends AbstractHttpStrategy
{
    /**
     * Contains the mandatory authentication information.
     */
    const X_HEADER_FIELD = 'x-idp-username';

    /**
     * @return string
     */
    public function apply(Request $request)
    {
        return $this->extractFieldInfo($request->headers, self::X_HEADER_FIELD);
    }
}
