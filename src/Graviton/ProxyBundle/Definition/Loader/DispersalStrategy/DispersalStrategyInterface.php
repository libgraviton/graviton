<?php
/**
 * Interface for all dispersal strategies
 */

namespace Graviton\ProxyBundle\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\ApiDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface DispersalStrategyInterface
{
    /**
     * may the strategy handle this input
     *
     * @param string $input input
     *
     * @return boolean
     */
    public function supports($input);

    /**
     * process
     *
     * @param string|null $input        Data to be processed
     * @param array       $fallbackData Default data (like host, basePath) to be used if not available in json.
     *
     * @return ApiDefinition
     */
    public function process($input, array $fallbackData = []);
}
