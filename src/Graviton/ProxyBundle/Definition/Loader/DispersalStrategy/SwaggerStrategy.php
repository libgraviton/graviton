<?php
/**
 * SwaggerStrategy
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;

/**
 * process a swagger.json file and return an APi definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SwaggerStrategy implements DispersalStrategyInterface
{
    /**
     * process data
     *
     * @param null|string $input input
     *
     * @return ApiDefinition
     */
    public function process($input)
    {

    }

    /**
     * is input data valid json
     *
     * @param string $input
     *
     * @return boolean
     */
    public function supports($input)
    {
        $this->decodeJson($input);

        // check if error occurred
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * decode a json string
     *
     * @param string $input
     *
     * @return mixed
     *
     */
    private function decodeJson($input)
    {
        $input = trim($input);

        return json_decode($input);
    }
}
