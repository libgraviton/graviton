<?php
/**
 * strategy for validating auth through the ip address.
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SameSubnetStrategy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SameSubnetStrategy extends AbstractHttpStrategy
{
    /** @var String */
    protected $subnet;

    /** @var bool pass through by default */
    protected $stopPropagation = false;

    /**
     * @param String $subnet      Subnet to be checked (e.g. 10.2.0.0/24)
     * @param String $headerField Http header field to be searched for the 'username'
     */
    public function __construct($subnet, $headerField = 'x-graviton-authentication')
    {
        $this->subnet= $subnet;
        $this->headerField = $headerField;
    }

    /**
     * Applies the defined strategy on the provided request.
     *
     * @param Request $request request to handle
     *
     * @return string
     */
    public function apply(Request $request)
    {
       // if (IpUtils::checkIp($request->getClientIp(), $this->subnet)) {
            $this->stopPropagation = true;
            return $this->determineName($request);
       // }

        throw new \InvalidArgumentException('Provided request information are not valid.');
    }

    /**
     * Decider to stop other strategies running after from being considered.
     *
     * @return boolean
     */
    public function stopPropagation()
    {
        return $this->stopPropagation;
    }

    /**
     * Provides the list of registered roles.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        return [SecurityUser::ROLE_USER, SecurityUser::ROLE_SUBNET];
    }

    /**
     * Finds the username either from a http header filed or returns a default.
     *
     * @param Request $request Current http request
     *
     * @return string
     */
    private function determineName(Request $request)
    {
        if ($request->headers->has($this->headerField)) {
            return $request->headers->get($this->headerField);
        }

        return 'graviton_subnet_user';
    }
}
