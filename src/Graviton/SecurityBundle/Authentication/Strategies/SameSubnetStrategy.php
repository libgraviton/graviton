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

    /** @var String */
    protected $headerField;

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
     * Ip subnet check
     * @param string $subnet IpAddress
     * @return void
     */
    public function setSubnetIp($subnet)
    {
        $this->subnet = $subnet;
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
        $ip = $request->getClientIp();
        if (IpUtils::checkIp($request->getClientIp(), $this->subnet)) {
            $name = $this->extractFieldInfo($request->headers, $this->headerField);
            if (!empty($name)) {
                return $name;
            }
        }

        return '';
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
}
