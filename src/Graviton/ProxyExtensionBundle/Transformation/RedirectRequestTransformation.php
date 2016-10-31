<?php
/**
 * RedirectRequestTransformation
 */

namespace Graviton\ProxyExtensionBundle\Transformation;

use Graviton\ProxyBundle\Transformation\RequestTransformationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RedirectRequestTransformation
 *
 * @package Graviton\ProxyExtensionBundle\Transformation
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class RedirectRequestTransformation implements RequestTransformationInterface
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
        list(,,$endpoint,,$path) = explode('/', $requestIn->getRequestUri(), 5);

        $options = $this->configuration['redirect'][$endpoint];

        $url = sprintf(
            '%s/%s',
            $options['uri'],
            $path
        );

        return Request::create(
            $url,
            'GET'
        );
    }
}
