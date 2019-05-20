<?php
/**
 * event that fires before we execute a querybuilder in rest context
 */

namespace Graviton\RestBundle\Event;

use Doctrine\MongoDB\Query\Builder;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ModelQueryEvent extends Event
{

    /**
     * @var string
     */
    public const NAME = 'document.model.event.query';

    /**
     * @var Builder
     */
    private $queryBuilder;

    /**
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->queryBuilder;
    }

    /**
     * @param Builder $queryBuilder
     */
    public function setQueryBuilder(Builder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }
}
