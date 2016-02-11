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
    /** @var RequestStack */
    protected $requestStack;

    /** @var string  */
    protected $extractUsername;

    /** @var string  */
    protected $extractCoreId;

    /** @var string  */
    protected $clientIdName;

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
        $bagValue = $this->extractFieldInfo($request->cookies, $this->field);

        $pattern = "/((?m)(?<=\b{$this->extractUsername}=)[^,]*)/i";
        preg_match($pattern, $bagValue, $matches);
        if (!$matches) {
            return $bagValue;
        }
        $fieldValue = $matches[0];

        if ($this->requestStack && $this->extractCoreId && $this->clientIdName) {
            $pattern = "/((?m)(?<=\b{$this->extractCoreId}=)[^,]*)/i";
            preg_match($pattern, $bagValue, $matches);
            if ($matches) {
                /** @var Request $request */
                $request = $this->requestStack->getCurrentRequest();
                $request->attributes->set($this->clientIdName, $matches[0]);
            }
        }

        return $fieldValue;
    }



    /**
     * Symfony Container
     *
     * @param RequestStack $requestStack    request object
     * @param string       $extractUsername identifier in posted params
     * @param string       $extractCoreId   client specific identifier
     * @param string       $idName          save to request attrivute name
     * @return void
     */
    public function setDynamicParameters(RequestStack $requestStack, $extractUsername, $extractCoreId, $idName)
    {
        $this->requestStack = $requestStack;
        $this->extractUsername = $extractUsername;
        $this->extractCoreId = $extractCoreId;
        $this->clientIdName = $idName;
    }
}
