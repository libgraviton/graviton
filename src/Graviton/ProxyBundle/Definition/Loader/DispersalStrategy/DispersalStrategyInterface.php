<?php
/**
 * Interface for all dispersal strategies
 */

namespace Graviton\ProxyBundle\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\ApiDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * @param string|null $input input
     *
     * @return ApiDefinition
     */
    public function process($input, array $fallbackData = []);
}
