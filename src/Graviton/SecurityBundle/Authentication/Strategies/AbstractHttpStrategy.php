<?php
/**
 * abstract strategy for checking auth against parts of the request
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AbstractHttpStrategy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractHttpStrategy implements StrategyInterface
{
    /**
     * Will stop propagation if params exists
     * @var bool
     */
    private $passed = false;

    /**
     * Extracts information from the a request header field.
     *
     * @param ParameterBag|HeaderBag $header    object representation of the request header.
     * @param string                 $fieldName Name of the field to be read.
     *
     * @return string
     */
    protected function extractFieldInfo($header, $fieldName)
    {
        if ($header instanceof ParameterBag || $header instanceof HeaderBag) {
            $this->validateField($header, $fieldName);
            return $header->get($fieldName, '');
        }

        throw new \InvalidArgumentException('Provided request information are not valid.');
    }

    /**
     * Verifies that the provided header has the expected/mandatory fields.
     *
     * @param ParameterBag|HeaderBag $header    object representation of the request header.
     * @param string                 $fieldName Name of the header field to be validated.
     *
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validateField($header, $fieldName)
    {
        $this->passed = $header->has($fieldName);
        if (!$this->passed) {
            return;
        }

        // get rid of anything not a valid character
        $authInfo = filter_var($header->get($fieldName), FILTER_SANITIZE_STRING);

        // get rid of whitespaces
        $patterns = array("\r\n", "\n", "\r", "\s", "\t");
        $authInfo = str_replace($patterns, "", trim($authInfo));

        // get rid of control characters
        if (empty($authInfo) || $authInfo !== preg_replace('#[[:cntrl:]]#i', '', $authInfo)) {
            throw new HttpException(
                Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED,
                'Mandatory header field (' . $fieldName . ') not provided or invalid.'
            );
        }
    }

    /**
     * Decider to stop other strategies running after from being considered.
     *
     * @return boolean
     */
    public function stopPropagation()
    {
        return $this->passed;
    }
}
