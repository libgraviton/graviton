<?php
/**
 * security consultant entity
 */

namespace Graviton\SecurityBundle\Entities;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class SecurityUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityUser implements UserInterface
{
    const ROLE_USER = 'ROLE_GRAVITON_USER';
    const ROLE_CONSULTANT = 'ROLE_GRAVITON_CONSULTANT';
    const ROLE_ANONYMOUS = 'ROLE_GRAVITON_ANONYMOUS';
    const ROLE_SUBNET = 'ROLE_GRAVITON_SUBNET_USER';
    const ROLE_TEST = 'ROLE_GRAVITON_TEST_USER';

    /**
     * @var Object
     */
    private $user;

    /**
     * @var Role[]
     */
    private $roles;


    /**
     * Constructor of the class.
     *
     * @param object $user  the user
     * @param Role[] $roles roles for the contract
     */
    public function __construct($user, array $roles = array())
    {
        $this->user = $user;
        $this->roles = $roles;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * @return null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        if (method_exists($this->user, 'getUsername')) {
            return $this->user->getUsername();
        }
        return false;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    /**
     * Provides the consultant object.
     *
     * @return Object
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Check if user has role
     * @param string $role User Role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $roles = $this->$this->getRoles();
        $username = $this->getUsername() ? $this->getUsername() : 'anonymous';
        return reset($roles).':'.$username;
    }
}
