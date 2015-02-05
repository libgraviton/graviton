<?php

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


/**
 * Class AbstractHttpStrategy
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractHttpStrategy implements StrategyInterface
{
    /**
     * Extracts information from the a request header field.
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag|\Symfony\Component\HttpFoundation\HeaderBag $header    object representation of the request header.
     *
     * @param  string                                                                                    $fieldname Name of the field to be read.
     *
     * @return string
     */
    protected function extractFieldInfo($header, $fieldname)
    {
        if ($header instanceof ParameterBag || $header instanceof HeaderBag) {

            $this->validateField($header, $fieldname);

            return $header->get($fieldname, '');
        }

        throw new \InvalidArgumentException('Provided request information are not valid.');
    }

    /**
     * Verifies that the provided header has the expected/mandatory fields.
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag|\Symfony\Component\HttpFoundation\HeaderBag $header    object representation of the request header.
     * @param string                                                                                     $fieldName Name of the header field to be validated.
     *
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validateField($header, $fieldName)
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
                'Mandatory header field (' . $fieldName . ') not provided or invalid.'
            );
        }
    }
}
