<?php
/**
 * Voter deciding, if the provided object is
 */
namespace Graviton\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ServiceAllowedVoter extends Voter
{
    /** @var array List of services always allowed to be called. */
    private $whitelist = array();

    /**
     * supported classes
     *
     * @var array
     */
    protected $supportedClasses = [
        'Symfony\Component\HttpFoundation\Request'
    ];

    /**
     * supported attributes
     *
     * @var array
     */
    protected $supportedAttributes = [
        'VIEW'
    ];

    /**
     * @param array $whiteList Set of services to be allowed to be called.
     */
    public function __construct($whiteList = array())
    {
        $this->whitelist = $whiteList;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return (isset($this->supportedAttributes[$attribute]) && isset($this->supportedClasses[$subject]));
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute attribute
     * @param mixed          $subject   subject
     * @param TokenInterface $token     token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return in_array($subject->getPathInfo(), $this->whitelist);
    }
}
