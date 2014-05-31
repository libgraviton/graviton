<?php

namespace Graviton\RestBundle\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseFactory
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ResponseFactory
{
    /**
     * instanciate a new Response object
     *
     * @param Integers $statusCode HTTP status code for response
     * @param string   $content    response string
     * @param array    $headers    Array of headers
     *
     * @return Response
     */
    public static function getResponse($statusCode, $content = '', $headers = array())
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setContent($content);

        foreach ($headers as $type => $values) {
            $response->headers->set($type, $values);
        }

        return $response;
    }
}
