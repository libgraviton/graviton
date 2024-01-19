<?php
/**
 * user provider
 */

namespace Graviton\SecurityBundle\Authentication;

use Graviton\SecurityBundle\Entities\AnonymousUser;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class UserProvider implements UserProviderInterface
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username the consultants username
     *
     * @return false|UserInterface
     */
    public function loadUserByIdentifier($username): UserInterface
    {
        if ($username == AnonymousUser::USERNAME) {
            return new AnonymousUser();
        }

        return new SecurityUser($username);
    }

    /**
     * not necessary
     *
     * @param UserInterface $user user to refresh
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    /**
     * always true here
     *
     * @param string $class class name
     *
     * @return bool true
     */
    public function supportsClass(string $class)
    {
        return true;
    }
}
