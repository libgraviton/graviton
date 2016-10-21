<?php

namespace Graviton\ProxyBundle\Transformation;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class FundinfoRequestTransformation
 *
 * @package Graviton\ProxyBundle\Transformation
 */
class FundinfoRequestTransformation implements RequestTransformationInterface
{
    /** @var array  */
    private $configuration;

    /**
     * FundinfoRequestTransformation constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function transformRequest(Request $requestIn, Request $requestOut)
    {
        $options = $this->configuration['custom']['fundinfo'];
        preg_match("@[^/]+$@", $requestIn->getRequestUri(), $pathItems);

        $queryString = str_replace("{shareClass}", $pathItems[0], $options['queryStringTemplate']);
        $queryString = str_replace("{documentType}", 'KID', $queryString);
        $queryString = str_replace("{language}", 'de', $queryString);

        $url = sprintf(
            '%s?%s&%s',
            $options['uri'],
            'apiKey='.$options['apiKey'],
            $queryString
        );

        return Request::create(
            $url,
            'GET'
        );
    }
}
