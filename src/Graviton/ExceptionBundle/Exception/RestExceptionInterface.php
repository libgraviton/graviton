<?php
/**
 * common rest exception interface
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface RestExceptionInterface
{
    /**
     * Set the response object (optional)
     *
     * @param Response $response Response object
     *
     * @return RestException $this This
     */
    public function setResponse(Response $response);

    /**
     * Get the response object
     *
     * @return Response $response Response object
     */
    public function getResponse();
}
