<?php
/**
 * Interface StrategyInterface
 */

namespace Graviton\SecurityBundle\EventListener\Strategies;

/**
 * Interface StrategyInterface
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface StrategyInterface
{
    /**
     * Shall enforce the defined strategy to be applied
     *
     * @param mixed $data Information to be processed.
     *
     * @return mixed
     */
    public function apply($data);

    /**
     * Provides an identifier of the current strategy
     *
     * It should be as unique as possible.
     *
     * @return string
     */
    public function getId();
}
