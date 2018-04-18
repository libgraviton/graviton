<?php
/**
 * Interface for analytics processor classes
 */
namespace Graviton\AnalyticsBundle;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface ProcessorInterface
{
    /**
     * processes the data
     *
     * @param array $data data
     *
     * @return array new data
     */
    public function process(array $data);
}
