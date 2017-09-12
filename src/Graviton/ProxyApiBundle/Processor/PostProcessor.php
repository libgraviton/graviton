<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\ProxyApiBundle\Processor;

use Graviton\ProxyApiBundle\Model\ProxyModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Before Proxy Request process and prepare data for orequest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PostProcessor implements PostProcessorInterface
{
    /**
     * For additional post processing of the response or
     * any other handling if required.
     *
     * @param Response   $response   Sf client response
     * @param ProxyModel $proxyModel Configuration model
     * @return Response
     */
    public function process(Response $response, ProxyModel $proxyModel)
    {
        return $response;
    }
}
