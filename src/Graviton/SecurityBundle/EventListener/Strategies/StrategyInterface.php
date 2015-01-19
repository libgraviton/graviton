<?php

namespace Graviton\SecurityBundle\EventListener\Strategies;

/**
 * Interface StrategyInterface
 *
 * @category GravitonSecutityBundle
 * @package  Graviton
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
