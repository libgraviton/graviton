<?php
/**
 * security contract entity
 */

namespace Graviton\SecurityBundle\Entities;

use GravitonDyn\ContractBundle\Document\Contract;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class SecurityContract
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityContract implements UserInterface
{
    /**
     * @var Contract
     */
    private $contract;

    /**
     * @var Role[]
     */
    private $roles;


    /**
     * Constructor of the class.
     *
     * @param Contract $contract contract
     * @param Role[]   $roles    roles for the contract
     */
    public function __construct(Contract $contract, array $roles = array())
    {
        $this->contract = $contract;
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
        return sprintf(
            "%s %s",
            $this->contract->getCustomer()->getFirstname(),
            $this->contract->getCustomer()->getLastname()
        );
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
}
