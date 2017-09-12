<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\ProxyApiBundle\Processor;

use Graviton\ProxyApiBundle\Model\ProxyModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * INTERFACE After Proxy Request process and prepare data for request
 * Can be used to handle custom error, remove unwanted response data, etc.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface PostProcessorInterface
{
    /**
     * For additional post processing of the response or
     * any other handling if required.
     *
     * @param Response   $response   Sf client response
     * @param ProxyModel $proxyModel Configuration model
     * @return Response
     */
    public function process(Response $response, ProxyModel $proxyModel);
}
