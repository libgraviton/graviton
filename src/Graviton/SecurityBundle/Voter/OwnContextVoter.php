<?php
/**
 * Voter deciding, if the provided object is
 */
namespace Graviton\SecurityBundle\Voter;

use GravitonDyn\ContractBundle\Document\Contract;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class OwnContextVoter implements VoterInterface
{
    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token      A ToketnInterface instance
     * @param object|null    $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        /** @var \GravitonDyn\ContractBundle\Document\Contract $contract */
        $contract = $token->getUser()->getContract();

        // $object is an account
        $grant = $this->grantByAccount($contract, $object)
            && $this->grantByCustomer($contract, $object);

        return true === $grant ? VoterInterface::ACCESS_ABSTAIN : VoterInterface::ACCESS_DENIED;
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
