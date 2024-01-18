<?php
/**
 * security AnonymousUser entity
 * A basic user to allow loggin, query and find object based on anonymous authentication.
 */

namespace Graviton\SecurityBundle\Entities;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AnonymousUser
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnonymousUser implements UserInterface
{

    /**
     * username constant
     */
    public const USERNAME = 'anonymous';

    /**
     * Returns the roles granted to the user.
     *
     * @return string[] The user roles
     */
    public function getRoles(): array
    {
        return [];
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
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
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
        return self::USERNAME;
    }
}
