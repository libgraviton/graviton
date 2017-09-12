<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\ProxyApiBundle\Processor;

use Graviton\ProxyApiBundle\Helper\HttpHelper;
use Graviton\ProxyApiBundle\Model\ProxyModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Before Proxy Request process and prepare data for orequest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PreProcessor implements PreProcessorInterface
{
    /**
     * Filter or change values of incoming request to be processed
     *
     * @param Request    $originalRequest Incoming current request
     * @param HttpHelper $httpHelper      Building the new request
     * @param ProxyModel $proxyModel      Configuration model
     * @return HttpHelper
     */
    public function process(
        Request $originalRequest,
        HttpHelper $httpHelper,
        ProxyModel $proxyModel
    ) {
        $httpHelper->setMethod($originalRequest->getMethod());

        $queryParams = (array) $originalRequest->query->all();

        // Query Params, it will remove unwanted params and map to new.
        if ($optParams = $proxyModel->getQueryParams()) {
            $newParamsString = http_build_query($optParams);
            foreach ($queryParams as $key => $value) {
                // Remove attempt to inject with %26 = & in value.
                $value = strpos($value, '&') !== false ? strstr($value, '&', true) : $value;
                $newParamsString = str_replace('%7B'.$key.'%7D', trim($value), $newParamsString);
            }
            parse_str($newParamsString, $queryParams);
        }

        // Map additional params, append additional params
        if ($optParams = $proxyModel->getQueryAdditionals()) {
            foreach ($optParams as $key => $value) {
                $queryParams[$key] = $value;
            }
        }

        // Add to HTTP Query Params
        foreach ($queryParams as $name => $value) {
            $httpHelper->addQueryParams($name, $value);
        }

        return $httpHelper;
    }
}
