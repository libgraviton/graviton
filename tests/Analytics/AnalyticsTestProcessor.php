<?php
/**
 * analytics processor used in tests
 */
namespace Graviton\Tests\Analytics;

use Graviton\AnalyticsBundle\ProcessorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticsTestProcessor implements ProcessorInterface
{

    /**
     * merges and sorts
     *
     * @param array $data   data
     * @param array $params user supplied params
     *
     * @return array
     */
    public function process(array $data, array $params = [])
    {
        // first, combine the data
        $workData = [];
        foreach ($data as $set => $singleSet) {
            $workData = array_merge($singleSet, $workData);
        }

        $sorter = array_map(
            function ($el) {
                return $el['sorter'];
            },
            $workData
        );

        array_multisort(
            $workData,
            SORT_ASC,
            SORT_NUMERIC,
            $sorter,
            SORT_ASC,
            SORT_NUMERIC
        );

        return $workData;
    }
}
