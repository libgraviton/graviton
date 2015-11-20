<?php
/**
 * LoaderInterface
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;

/**
 * LoaderInterface
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
interface LoaderInterface
{
    /**
     * set a load strategy
     *
     * @param DispersalStrategyInterface $strategy strategy to add
     *
     * @return LoaderInterface
     */
    public function setDispersalStrategy($strategy);

    /**
     * check if the input is supported
     *
     * @param string $input input
     *
     * @return boolean
     */
    public function supports($input);

    /**
     * @param string|null $input input
     *
     * @return ApiDefinition
     */
    public function load($input);
}
