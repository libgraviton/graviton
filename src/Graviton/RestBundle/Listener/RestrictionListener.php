<?php
/**
 * model event listener that restricts data access based on http headers
 */

namespace Graviton\RestBundle\Listener;

use Graviton\AnalyticsBundle\Event\PreAggregateEvent;
use Graviton\ExceptionBundle\Exception\RestrictedIdCollisionException;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\SearchNode;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionListener
{

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SecurityUtils
     */
    private SecurityUtils $securityUtils;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var bool
     */
    private bool $persistRestrictions;

    /**
     * @var bool
     */
    private bool $restrictSolr;

    /**
     * HttpHeader constructor.
     *
     * @param LoggerInterface $logger              logger
     * @param SecurityUtils   $securityUtils       security utils
     * @param RequestStack    $requestStack        request stack
     * @param bool            $persistRestrictions true to save the restrictions value to the entity (default)
     * @param bool            $restrictSolr        if we should restrict on solr queries
     */
    public function __construct(
        LoggerInterface $logger,
        SecurityUtils $securityUtils,
        RequestStack $requestStack,
        bool $persistRestrictions = true,
        bool $restrictSolr = true
    ) {
        $this->logger = $logger;
        $this->securityUtils = $securityUtils;
        $this->requestStack = $requestStack;
        $this->persistRestrictions = $persistRestrictions;
        $this->restrictSolr = $restrictSolr;
    }

    /**
     * gets called before a QueryBuilder is executed
     *
     * @param ModelQueryEvent $event Event
     *
     * @return void|null
     */
    public function onModelQuery(ModelQueryEvent $event)
    {
        if (!$this->securityUtils->hasDataRestrictions()) {
            return $event;
        }

        $builder = $event->getQueryBuilder();
        $filterValue = [];

        foreach ($this->securityUtils->getRequestDataRestrictions() as $fieldName => $fieldValue) {
            if ($fieldValue == null) {
                continue;
            }

            $inValue = [null, $fieldValue];
            $filterValue[$fieldName] = $inValue;

            if ($this->securityUtils->getDataRestrictionMode() == SecurityUtils::DATA_RESTRICTION_MODE_LTE) {
                $builder->addAnd(
                    $builder->expr()->addOr(
                        $builder->expr()->field($fieldName)->equals(null),
                        $builder->expr()->field($fieldName)->lte($fieldValue)
                    )
                );
            } else {
                $builder->addAnd(
                    $builder->expr()->field($fieldName)->in($inValue)
                );
            }
        }

        $this->logger->info(
            'RESTRICTION onModelQuery',
            [
                'filter' => $filterValue,
                'mode' => $this->securityUtils->getDataRestrictionMode()
            ]
        );

        $event->setQueryBuilder($builder);
    }

    /**
     * gets called before we persist an entity
     *
     * @param EntityPrePersistEvent $event event
     *
     * @return EntityPrePersistEvent event
     */
    public function onEntityPrePersistOrDelete(EntityPrePersistEvent $event)
    {
        // do we have an identity? -> set on entity!
        if (!$this->securityUtils->hasDataRestrictions() ||
            !($event->getEntity() instanceof \ArrayAccess)
        ) {
            return $event;
        }

        if (!$this->persistRestrictions) {
            $this->logger->info(
                'RESTRICTION onPrePersist DISABLED'
            );
            return $this->setChangeTrackingData($event);
        }

        $entity = $event->getEntity();
        $entityId = $entity['id'];

        foreach ($this->securityUtils->getRequestDataRestrictions() as $fieldName => $fieldValue) {
            $currentTenant = $fieldValue;
            if (!is_null($entityId)) {
                $currentTenant = $this->getCurrentTenant($event, $entityId, $fieldName, $fieldValue);
            }

            /**
             * if our restriction is null -> user is admin -> can see and modify all
             * if restriction has value -> collision exception if unequal to stored value
             */

            if ($fieldValue !== null && $fieldValue != $currentTenant) {
                throw new RestrictedIdCollisionException();
            }

            $this->logger->info(
                'RESTRICTION onPrePersist',
                [
                    'field' => $fieldName,
                    'value' => $currentTenant,
                    'mode' => $this->securityUtils->getDataRestrictionMode()
                ]
            );

            $entity[$fieldName] = $currentTenant;
        }

        $event->setEntity($entity);

        return $event;
    }

    /**
     * gets called before an aggregate pipeline is executed
     *
     * @param PreAggregateEvent $event event
     *
     * @return PreAggregateEvent event
     */
    public function onPreAggregate(PreAggregateEvent $event)
    {
        if (!$this->securityUtils->hasDataRestrictions()) {
            return $event;
        }

        $matchConditions = [];
        $projectStage = [];

        foreach ($this->securityUtils->getRequestDataRestrictions() as $fieldName => $fieldValue) {
            $projectStage[$fieldName] = 0;
            if (is_null($fieldValue)) {
                continue;
            }
            if ($this->securityUtils->getDataRestrictionMode() == SecurityUtils::DATA_RESTRICTION_MODE_LTE) {
                $matchConditions[] = [
                    '$or' => [
                        [$fieldName => null],
                        [$fieldName => ['$lte' => $fieldValue]],
                    ]
                ];
            } else {
                // eq
                $matchConditions[] = [
                    $fieldName => ['$in' => [$fieldValue, null]]
                ];
            }
        }

        $newPipeline = [];
        if (!empty($matchConditions)) {
            $newPipeline[] = [
                '$match' => [
                    '$and' => $matchConditions
                ]
            ];
        }
        if (!empty($projectStage)) {
            $newPipeline[] = ['$project' => $projectStage];
        }

        $newPipeline = array_merge(
            $newPipeline,
            $event->getPipeline()
        );

        $this->logger->info(
            'RESTRICTION onPreAggregate',
            [
                'pipeline' => $newPipeline,
                'mode' => $this->securityUtils->getDataRestrictionMode()
            ]
        );

        $event->setPipeline($newPipeline);

        return $event;
    }

    /**
     * gets called when a rql search is done..
     *
     * @param VisitNodeEvent $event event
     *
     * @return VisitNodeEvent event
     */
    public function onRqlSearch(VisitNodeEvent $event)
    {
        if (!$this->securityUtils->hasDataRestrictions() || !$event->getNode() instanceof SearchNode) {
            return $event;
        }

        if (!$this->restrictSolr) {
            $this->logger->info('RESTRICTION onRqlSearch DISABLED');
            return $event;
        }

        /** @var $node SearchNode */
        $node = $event->getNode();

        foreach ($this->securityUtils->getRequestDataRestrictions() as $fieldName => $fieldValue) {
            if (is_null($fieldValue)) {
                continue;
            }

            $this->logger->info(
                'RESTRICTION onRqlSearch',
                [
                    'field' => $fieldName,
                    'value' => $fieldValue,
                    'mode' => $this->securityUtils->getDataRestrictionMode()
                ]
            );

            $node->addSearchTerm($fieldName.':'.$fieldValue);
            $node->setVisited(false);
        }

        $event->setNode($node);
    }

    /**
     * gets the current tenant that is saved on the entity. if it doesn't exist, return $checkValue
     *
     * @param EntityPrePersistEvent $event      event
     * @param mixed                 $entityId   entity id
     * @param string                $checkField field to check
     * @param mixed                 $checkValue value to check for on the field
     *
     * @throws \LogicException
     *
     * @return void
     */
    private function getCurrentTenant(EntityPrePersistEvent $event, $entityId, $checkField, $checkValue)
    {
        $queryBuilder = $event
            ->getRepository()
            ->createQueryBuilder()
            ->field('id')->equals($entityId)
            ->select([$checkField])
            ->limit(1)
            ->hydrate(false);

        $result = $queryBuilder->getQuery()->getSingleResult();

        // record doesn't exist -> return $checkValue to persist
        if ($result === null) {
            return $checkValue;
        }

        // field doesn't exist -> assume global admin record!
        if (!isset($result[$checkField])) {
            return null;
        }

        return $result[$checkField];
    }
}
