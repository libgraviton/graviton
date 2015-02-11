<?php

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class CookieFieldStrategy
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CookieFieldStrategy extends AbstractHttpStrategy
{
    /**
     * Contains the mandatory authentication information.
     */
    const COOKIE_FIELD = 'username';

    /**
     * Applies the defined strategy on the provided request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    public function apply(Request $request)
    {
        return $this->extractFieldInfo($request->cookies, self::COOKIE_FIELD);
    }
}
