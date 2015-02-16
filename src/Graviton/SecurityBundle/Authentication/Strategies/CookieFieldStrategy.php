<?php
/**
 * authentification strategy based on a username cookie
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class CookieFieldStrategy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
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
     * @param Request $request request to handle
     *
     * @return string
     */
    public function apply(Request $request)
    {
        return $this->extractFieldInfo($request->cookies, self::COOKIE_FIELD);
    }
}
