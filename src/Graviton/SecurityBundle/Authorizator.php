<?php

namespace Graviton\SecurityBundle;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class Authorizator
{
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;


    /**
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Determines if the VIEW permission is granted.
     *
     * @param object $object
     *
     * @return bool
     */
    public function canView($object)
    {
        return $this->authorizationChecker->isGranted('VIEW', $object);
    }

    /**
     * Determines if the CREATE permission is granted.
     *
     * @param object $object
     *
     * @return bool
     */
    public function canCreate($object)
    {
        return $this->authorizationChecker->isGranted('CREATE', $object);
    }

    /**
     * Determines if the DELETE permission is granted.
     *
     * @param object $object
     *
     * @return bool
     */
    public function canDelete($object)
    {
        return $this->authorizationChecker->isGranted('DELETE', $object);
    }

    /**
     * Determines if the UPDATE permission is granted.
     *
     * @param object $object
     *
     * @return bool
     */
    public function canUpdate($object)
    {
        return $this->authorizationChecker->isGranted('UPDATE', $object);
    }
}
