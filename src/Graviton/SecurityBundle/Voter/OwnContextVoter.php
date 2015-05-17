<?php
/**
 * Voter deciding, if the provided object is
 */
namespace Graviton\SecurityBundle\Voter;

use GravitonDyn\ContractBundle\Document\Contract;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class OwnContextVoter extends AbstractVoter
{
    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array(
            'GravitonDyn\AccountBundle\Document\Account',
            'GravitonDyn\CustomerBundle\Document\Customer',
        );
    }

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute
     *
     * @return array an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    protected function getSupportedAttributes()
    {
        return array(
            'VIEW',
            'CREATE',
            'EDIT',
            'DELETE',
        );
    }

    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user)
     *
     * @param string               $attribute The attribute to be checked against.
     * @param object               $object    The object the access shall be granted for.
     * @param UserInterface|string $user      The user asking for permission.
     *
     * @return bool
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if (null === $user || !($user instanceof \Graviton\SecurityBundle\Entities\SecurityContract)) {
            return false;
        }

        /** @var \GravitonDyn\ContractBundle\Document\Contract $contract */
        $contract = $user->getContract();

        return $this->grantByAccount($contract, $object)
            || $this->grantByCustomer($contract, $object);
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
