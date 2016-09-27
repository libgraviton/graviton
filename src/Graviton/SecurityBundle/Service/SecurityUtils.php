<?php
/**
 * Simple service helper to find and user Security User
 */
namespace Graviton\SecurityBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Graviton\SecurityBundle\Entities\SecurityUser;

/**
 * Service Security Helper
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SecurityUtils
{
    /** @var SecurityUser */
    private $securityUser;
    
    /** @var string  */
    private $requestId;

    /**
     * StoreManager constructor.
     * @param TokenStorage $tokenStorage Sf Auth token storage
     */
    public function __construct(
        TokenStorage $tokenStorage
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
            && ($user = $token->getUser()) instanceof UserInterface ) {
            $this->securityUser = $user;
            return true;
        }
        return false;
    }
    
    /**
     * Find current user
     *
     * @return string|bool
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

    /**
     * Get current unique request ID
     *
     * @return string
     */
    public function getRequestId()
    {
        if ($this->requestId) {
            return $this->requestId;
        }
        return $this->requestId = $this->generateUuid();
    }

    /**
     * Generate a unique UUID.
     *
     * @return string
     */
    private function generateUuid()
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            $this->requestId = uniqid('unq', true);
        } else {
            $data = openssl_random_pseudo_bytes(16);
            // set version to 0100
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            // set bits 6-7 to 10
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $this->requestId = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        return $this->requestId;
    }
}
