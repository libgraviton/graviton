<?php
namespace Graviton\RestBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
	public static function getResponse($statusCode, $content = array(), $headers = array())
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
