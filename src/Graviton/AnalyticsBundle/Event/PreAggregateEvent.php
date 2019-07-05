<?php
/**
 * event that fires before we execute an aggregation pipeline
 */

namespace Graviton\AnalyticsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class PreAggregateEvent extends Event
{

    /**
     * @var string
     */
    public const NAME = 'analytics.event.pre_aggregate';

    /**
     * @var array
     */
    private $pipeline = [];

    /**
     * @return array pipeline
     */
    public function getPipeline(): array
    {
        return $this->pipeline;
    }

    /**
     * @param array $pipeline pipeline
     *
     * @return void
     */
    public function setPipeline(array $pipeline): void
    {
        $this->pipeline = $pipeline;
    }
}
