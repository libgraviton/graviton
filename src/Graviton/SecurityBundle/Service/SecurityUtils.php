<?php
/**
 * Simple service helper to find and user Security User
 */
namespace Graviton\SecurityBundle\Service;

use Graviton\CoreBundle\Util\CoreUtils;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * data restriction mode constants
     */
    public const DATA_RESTRICTION_MODE_EQ = 'eq';
    public const DATA_RESTRICTION_MODE_LTE = 'lte';

    /**
     * @var array
     */
    private $dataRestrictionMap = [];

    /**
     * @var string
     */
    private $dataRestrictionMode;

    /**
     * StoreManager constructor.
     *
     * @param TokenStorageInterface $tokenStorage        Auth token storage
     * @param RequestStack          $requestStack        request stack
     * @param array                 $dataRestrictionMap  data restriction map
     * @param string                $dataRestrictionMode restriction mode (EQ for equals check or LTE for lessthanequal)
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ?array $dataRestrictionMap = [],
        $dataRestrictionMode = self::DATA_RESTRICTION_MODE_EQ
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;

        if ($dataRestrictionMode != self::DATA_RESTRICTION_MODE_EQ &&
            $dataRestrictionMode != self::DATA_RESTRICTION_MODE_LTE
        ) {
            throw new \RuntimeException("Restriction Mode '".$dataRestrictionMode."' is invalid!");
        }

        $this->setDataRestrictionMap($dataRestrictionMap);
        $this->dataRestrictionMode = $dataRestrictionMode;
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

    /**
     * set DataRestrictionMap
     *
     * @param array $dataRestrictionMap dataRestrictionMap
     *
     * @return void
     */
    private function setDataRestrictionMap(array $dataRestrictionMap)
    {
        if (!is_array($dataRestrictionMap)) {
            return;
        }

        foreach ($dataRestrictionMap as $headerName => $fieldName) {
            $fieldSpec = CoreUtils::parseStringFieldList($fieldName);
            if (count($fieldSpec) != 1) {
                throw new \LogicException("Wrong data restriction value as '${headerName}' '${fieldName}'");
            }

            $this->dataRestrictionMap[$headerName] = array_pop($fieldSpec);
        }
    }

    /**
     * returns if the current configuration specifies data restrictions
     *
     * @return bool true if yes, false otherwise
     */
    public function hasDataRestrictions()
    {
        return !empty($this->dataRestrictionMap);
    }

    /**
     * get DataRestrictionMap
     *
     * @return array DataRestrictionMap
     */
    public function getDataRestrictionMap()
    {
        return $this->dataRestrictionMap;
    }

    /**
     * get DataRestrictionMode
     *
     * @return string DataRestrictionMode
     */
    public function getDataRestrictionMode()
    {
        return $this->dataRestrictionMode;
    }

    /**
     * gets the restrictions in an finalized array structure
     *
     * @return array restrictions
     */
    public function getRequestDataRestrictions()
    {
        $restrictions = [];
        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($headerName, null);
            if (!is_null($headerValue) && $fieldSpec['type'] == 'int') {
                $headerValue = (int) $headerValue;
            }
            $restrictions[$fieldSpec['name']] = $headerValue;
        }
        return $restrictions;
    }
}
