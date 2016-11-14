<?php
/**
 * strategy for validating auth through the x-idp-username header
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Class HeaderFieldStrategy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HeaderFieldStrategy extends AbstractHttpStrategy
{
    /** @var String */
    protected $field;

    /**
     * @param String $field field
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * Applies the defined strategy on the provided request.
     * Value may contain a coma separated string values, we use first as identifier.
     *
     * @param Request $request request to handle
     *
     * @return string
     */
    public function apply(Request $request)
    {
        return $this->extractFieldInfo($request->headers, $this->field);
    }

    /**
     * Provides the list of registered roles.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        return [SecurityUser::ROLE_USER];
    }
}
