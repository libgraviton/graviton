<?php
/**
 * Voter deciding, if the provided object is
 */
namespace Graviton\SecurityBundle\Voter;

use GravitonDyn\ContractBundle\Document\Contract;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class OwnContextVoter extends Voter
{

    /**
     * supported classes
     *
     * @var array
     */
    protected $supportedClasses = [
        'GravitonDyn\AccountBundle\Document\Account',
        'GravitonDyn\CustomerBundle\Document\Customer',
    ];

    /**
     * supported attributes
     *
     * @var array
     */
    protected $supportedAttributes = [
        'VIEW',
        'CREATE',
        'EDIT',
        'DELETE'
    ];

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
        $user = $token->getUser();

        if (null === $user || !($user instanceof \Graviton\SecurityBundle\Entities\SecurityContract)) {
            return false;
        }

        /** @var \GravitonDyn\ContractBundle\Document\Contract $contract */
        $contract = $user->getContract();

        return $this->grantByAccount($contract, $subject)
            || $this->grantByCustomer($contract, $subject);
    }

    /**
     * Determines, if the given object is of type Account and if it in the set of accounts related to the contract.
     *
     * @param Contract $contract The current contract identified by provided the access token.
     * @param mixed    $object   The object to be handled
     *
     * @return bool
     */
    protected function grantByAccount(Contract $contract, $object)
    {
        if ($object instanceof \GravitonDyn\AccountBundle\Document\Account) {
            return $contract->getAccount()->contains($object);
        }

        return false;
    }

    /**
     * Determines, if the given object is of type Customer and if it is related to the contract.
     *
     * @param Contract $contract The current contract identified by provided the access token.
     * @param mixed    $object   The object to be handled
     *
     * @return bool
     */
    protected function grantByCustomer(Contract $contract, $object)
    {
        if ($object instanceof \GravitonDyn\CustomerBundle\Document\Customer) {
            return $contract->getCustomer() == $object;
        }

        return false;
    }
}
