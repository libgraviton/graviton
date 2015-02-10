<?php
/**
 * Class HeaderExtractStrategy
 *
 * PHP Version 5
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */

namespace Graviton\SecurityBundle\EventListener\Strategies;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class HeaderExtractStrategy
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
final class HeaderExtractStrategy implements StrategyInterface
{
    /**
     * Contains the mandatory authentication information.
     *
     * @var array
     */
    private $mandatoryHeaderFields = array(
        'x-idp-usernameInhalt',
    );

    /**
     * Shall enforce the defined strategy to be applied
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Information to be processed.
     *
     * @return array
     */
    public function apply($request)
    {
        if (!$request instanceof Request) {
            throw new \InvalidArgumentException(
                'Provided data to be scanned for authentication is not a \Symfony\Component\HttpFoundation\Request',
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->extractFieldInfo($request->headers);
    }

    /**
     * Extracts every mandatroy field from the request header.
     *
     * @param \Symfony\Component\HttpFoundation\HeaderBag $header object representation of the request header.
     *
     * @return array
     */
    private function extractFieldInfo(HeaderBag $header)
    {
        $info = array();

        foreach ($this->mandatoryHeaderFields as $field) {
            $this->validateField($header, $field);

            $info[$field] = $header->get($field);
        }

        return $info;
    }

    /**
     * Verifies that the provided header has the expected/mandatory fields.
     *
     * @param \Symfony\Component\HttpFoundation\HeaderBag $header    object representation of the request header.
     * @param string                                      $fieldName Name of the header field to be validated.
     *
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validateField(HeaderBag $header, $fieldName)
    {
        $passed = $header->has($fieldName);

        // get rid of anything not a valid character
        $authInfo = filter_var($header->get($fieldName), FILTER_SANITIZE_STRING);

        if (false !== $passed && !empty($authInfo)) {
            $passed = true;
        }

        // get rid of control characters
        if (false !== $passed && $authInfo === preg_replace('#[[:cntrl:]]#i', '', $authInfo)) {
            $passed = true;
        }

        if (false === $passed) {
            throw new HttpException(
                Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED,
                'Mandatory header field (' . $fieldName . ') not provided.'
            );
        }
    }

    /**
     * Provides an identifier of the current strategy
     *
     * It should be as unique as possible.
     *
     * @return string
     */
    public function getId()
    {
        return '\Graviton\SecurityBundle\EventListener\HeaderExtractStrategy';
    }
}
