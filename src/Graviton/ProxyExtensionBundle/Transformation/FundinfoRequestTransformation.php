<?php
/**
 * FundinfoRequestTransformation
 */

namespace Graviton\ProxyExtensionBundle\Transformation;

use Graviton\ProxyBundle\Transformation\RequestTransformationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FundinfoRequestTransformation
 *
 * @package Graviton\ProxyExtensionBundle\Transformation
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class FundinfoRequestTransformation implements RequestTransformationInterface
{
    /** @var array  */
    private $configuration;

    /**
     * FundinfoRequestTransformation constructor.
     *
     * @param array $configuration Configuration options
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     *
     * @param Request $requestIn  Currently incoming request
     * @param Request $requestOut Transformed request
     *
     * @return Request
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
