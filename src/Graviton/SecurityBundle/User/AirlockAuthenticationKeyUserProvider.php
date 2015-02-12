<?php
/**
 * airlock authkey based user provider
 */

namespace Graviton\SecurityBundle\User;

use Graviton\SecurityBundle\Entities\SecurityContract;
use GravitonDyn\ContractBundle\Document\Contract;
use \Graviton\RestBundle\Model\ModelInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class AirlockAuthenticationKeyUserProvider
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AirlockAuthenticationKeyUserProvider implements UserProviderInterface
{
    /**
     * @var \Graviton\RestBundle\Model\ModelInterface
     */
    private $documentModel;

    /**
     * @param \Graviton\RestBundle\Model\ModelInterface $contract contract to use as documentModel
     */
    public function __construct(ModelInterface $contract)
    {
        $this->documentModel = $contract;
    }

    /**
     * Finds a contract based on the provided ApiKey.
     *
     * @param string $apiKey key from airlock
     *
     * @return string
     */
    public function getUsernameForApiKey($apiKey)
    {
        $contractId = '';

        /** @var \GravitonDyn\ContractBundle\Document\Contract $contract */
        $contract = $this->documentModel->getRepository()->findOneBy(array('number' => $apiKey));

        if ($contract instanceof Contract) {
            $contractId = $contract->getId();
        }

        return $contractId;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $contractId contract id we need a username for
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @see \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($contractId)
    {
        // TODO [lapistano] to what is the contract to be mapped against??

        /** @var \GravitonDyn\ContractBundle\Document\Contract $contracts */
        $contract = $this->documentModel->find($contractId);

        if ($contract instanceof Contract) {
            // TODO [lapistano]: map the found contract to whatever ...
            return new SecurityContract($contract, $this->getContractRoles($contract));
        }

        throw new UsernameNotFoundException();
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user user to refresh
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class class to check for support
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class instanceof \Symfony\Component\Security\Core\User\UserInterface;
    }

    /**
     * Decides the role set the provided contract has.
     *
     * @param Contract $contract provided contract
     *
     * @return string[]
     */
    private function getContractRoles(Contract $contract)
    {
        // TODO [lapistano]: implement the ability to decide what roles the contract entity haas.

        return array('ROLE_GRAVITON_USER');
    }
}
