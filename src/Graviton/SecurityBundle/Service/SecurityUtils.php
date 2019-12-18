<?php
/**
 * Simple service helper to find and user Security User
 */
namespace Graviton\SecurityBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Graviton\SecurityBundle\Entities\SecurityUser;

/**
 * Service Security Helper
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SecurityUtils
{
    /**
     * @var SecurityUser
     */
    private $securityUser;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * StoreManager constructor.
     * @param TokenStorageInterface $tokenStorage Auth token storage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Check if there is a security user
     *
     * @return bool
     */
    public function isSecurityUser()
    {
        if ($this->securityUser) {
            return true;
        }

        /** @var PreAuthenticatedToken $token */
        if (($token = $this->tokenStorage->getToken())
            && ($user = $token->getUser()) instanceof UserInterface) {
            $this->securityUser = $user;
            return true;
        }
        return false;
    }

    /**
     * Find current user
     *
     * @return string
     * @throws UsernameNotFoundException
     */
    public function getSecurityUser()
    {
        if ($this->isSecurityUser()) {
            return $this->securityUser;
        }
        throw new UsernameNotFoundException('No security user');
    }

    /**
     * Return users username
     *
     * @return string
     * @throws UsernameNotFoundException
     */
    public function getSecurityUsername()
    {
        if ($this->isSecurityUser()) {
            return $this->securityUser->getUsername();
        }
        throw new UsernameNotFoundException('No security user');
    }

    /**
     * Check if current user is in Role
     *
     * @param string $role User role expected
     * @return bool
     * @throws UsernameNotFoundException
     */
    public function hasRole($role)
    {
        if ($this->isSecurityUser()) {
            return (bool) $this->securityUser->hasRole($role);
        }
        throw new UsernameNotFoundException('No security user');
    }
}
