<?php
/**
 * Voter deciding, if the provided object is
 */
namespace Graviton\SecurityBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ServiceAllowedVoter extends AbstractVoter
{
    /** @var array List of services always allowed to be called. */
    private $whitelist = array();


    /**
     * @param array $whiteList Set of services to be allowed to be called.
     */
    public function __construct(array $whiteList = NULL)
    {
        $this->whitelist = empty($whiteList)? array(): $whiteList;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array(
            'Symfony\Component\HttpFoundation\Request'
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
            'VIEW'
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
        return in_array($object->getPathInfo(), $this->whitelist);
    }
}
