<?php
/**
 * normal user
 */

namespace Graviton\SecurityBundle\Entities;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class SecurityUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SecurityUser implements UserInterface
{

    /**
     * @var string
     */
    private $username;

    /**
     * @var string[]
     */
    private $roles;

    /**
     * Constructor of the class.
     *
     * @param string   $username username
     * @param string[] $roles    roles for the contract
     */
    public function __construct(string $username, array $roles = array())
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return string[] The user roles
     */
    public function getRoles(): array
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
        return $this->getUserIdentifier();
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
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
     * Check if user has role
     * @param string $role User Role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }
}
