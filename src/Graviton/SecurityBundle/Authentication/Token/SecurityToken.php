<?php
/**
 * Enhanced PreAuthenticatedToken
 */

namespace Graviton\SecurityBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Class SecurityToken
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityToken extends PreAuthenticatedToken
{
    /**
     * Determines the token has the provided role.
     *
     * @param Role|string $role Role to be checked.
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        $inList = false;

        if (is_string($role)) {
            $role = new Role($role);
        }

        foreach ($this->getRoles() as $listedRole) {
            if ($role->getRole() === $listedRole->getRole()) {
                $inList = true;
                break;
            }
        }

        return $inList;
    }
}
