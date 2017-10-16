<?php
/**
 * Simple dummy provider for test cases.
 */
namespace Graviton\SecurityBundle\Authentication\Provider;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AirlockAuthenticationKeyConsultantProvider
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationProviderDummy extends AuthenticationProvider
{

    /**
     * AuthenticationProviderDummy constructor.
     */
    public function __construct()
    {
    }

    /**
     * Dummy loader for test case
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username the consultants username
     *
     * @return false|UserInterface
     */
    public function loadUserByUsername($username)
    {
        if ($username !== 'testUsername') {
            return false;
        }

        /** @var UserInterface $user */
        $user = new \stdClass();
        $user->id = 123;
        $user->username = $username;
        $user->firstName = 'aName';
        $user->lastName = 'aSurname';

        return $user;
    }
}
