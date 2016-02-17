<?php
/**
 * authentification strategy based on a username cookie
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CookieFieldStrategy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CookieFieldStrategy extends AbstractHttpStrategy
{
    /** @var string  */
    const COOKIE_FIELD_NAME = 'username';

    /** @var string  */
    const COOKIE_VALUE_CORE_ID = 'finnova_id';

    /** @var string  */
    const CONFIGURATION_PARAMETER_ID = 'graviton.security.core_id';

    /** @var string */
    protected $field;

    /**
     * @param string $field cookie field to be examined
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
        $bagValue = $this->extractFieldInfo($request->cookies, $this->field);

        // this needs to be available in a later state of the application
        $this->extractCoreId($request, $bagValue);

        return $this->extractAdUsername($bagValue);
    }

    /**
     * Finds and extracts the ad username from the cookie.
     *
     * @param string $value
     *
     * @return string
     */
    protected function extractAdUsername($value)
    {
        $pattern = "/((?m)(?<=\b".self::COOKIE_FIELD_NAME."=)[^,]*)/i";
        preg_match($pattern, $value, $matches);

        return (!$matches)? $value : $matches[0];
    }

    /**
     * Finds and extracts the core system id from tha cookie.
     *
     *
     * @param Request $request Request stack that controls the lifecycle of requests
     * @param string  $text    String to be examined for the core id.
     */
    protected function extractCoreId(Request $request, $text)
    {
        $pattern = "/((?m)(?<=\b".self::COOKIE_VALUE_CORE_ID."=)[^,]*)/i";
        preg_match($pattern, $text, $matches);

        if ($matches) {
            $request->attributes->set(self::CONFIGURATION_PARAMETER_ID, $matches[0]);
        }
    }
}
