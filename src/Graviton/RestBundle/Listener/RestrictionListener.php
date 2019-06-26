<?php
/**
 * model event listener that restricts data access based on http headers
 */

namespace Graviton\RestBundle\Listener;

use Graviton\AnalyticsBundle\Event\PreAggregateEvent;
use Graviton\CoreBundle\Util\CoreUtils;
use Graviton\ExceptionBundle\Exception\RestrictedIdCollisionException;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\SearchNode;
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
    private $logger;

    /**
     * @var array
     */
    private $dataRestrictionMap = [];

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * HttpHeader constructor.
     *
     * @param LoggerInterface $logger             logger
     * @param array           $dataRestrictionMap data restriction configuration
     * @param RequestStack    $requestStack       request stack
     */
    public function __construct(LoggerInterface $logger, ?array $dataRestrictionMap, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->setDataRestrictionMap($dataRestrictionMap);
        $this->requestStack = $requestStack;
    }

    /**
     * set DataRestrictionMap
     *
     * @param array $dataRestrictionMap dataRestrictionMap
     *
     * @return void
     */
    public function setDataRestrictionMap(?array $dataRestrictionMap)
    {
        if (!is_array($dataRestrictionMap)) {
            return;
        }

        foreach ($dataRestrictionMap as $headerName => $fieldName) {
            $fieldSpec = CoreUtils::parseStringFieldList($fieldName);
            if (count($fieldSpec) != 1) {
                throw new \LogicException("Wrong data restriction value as '${headerName}' '${fieldName}'");
            }

            $this->dataRestrictionMap[$headerName] = array_pop($fieldSpec);
        }
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
        if (!is_array($this->dataRestrictionMap) || empty($this->dataRestrictionMap)) {
            return null;
        }

        $builder = $event->getQueryBuilder();

        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($headerName, null);

            if ($headerValue == null) {
                continue;
            }

            if ($fieldSpec['type'] == 'int') {
                $headerValue = (int) $headerValue;
            }

            $fieldName = $fieldSpec['name'];
            $fieldValue = [null, $headerValue];

            $this->logger->info('RESTRICTION onModelQuery', ['field' => $fieldName, 'value' => $fieldValue]);

            $builder->addAnd(
                $builder->expr()->field($fieldName)->in($fieldValue)
            );
        }

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
        if (!is_array($this->dataRestrictionMap) ||
            empty($this->dataRestrictionMap) ||
            !($event->getEntity() instanceof \ArrayAccess)
        ) {
            return;
        }

        $entity = $event->getEntity();
        $entityId = $entity['id'];

        foreach ($this->getRestrictions() as $fieldName => $fieldValue) {
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

            $this->logger->info('RESTRICTION onPrePersist', ['field' => $fieldName, 'value' => $currentTenant]);

            // persist tenant again!
            $entity[$fieldName] = $currentTenant;

            if (is_null($fieldValue)) {
                continue;
            }
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
        if (!is_array($this->dataRestrictionMap) ||
            empty($this->dataRestrictionMap)
        ) {
            return;
        }

        $matchStage = [];
        $projectStage = [];

        foreach ($this->getRestrictions() as $fieldName => $fieldValue) {
            $projectStage[$fieldName] = 0;
            if (is_null($fieldValue)) {
                continue;
            }
            $matchStage[$fieldName] = ['$in' => [$fieldValue, null]];
        }

        $newPipeline = [];
        if (!empty($matchStage)) {
            $newPipeline[] = ['$match' => $matchStage];
        }
        if (!empty($projectStage)) {
            $newPipeline[] = ['$project' => $projectStage];
        }

        $newPipeline = array_merge(
            $newPipeline,
            $event->getPipeline()
        );

        $this->logger->info('RESTRICTION onPreAggregate', ['pipeline' => $newPipeline]);

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
        if (!$event->getNode() instanceof SearchNode) {
            return $event;
        }

        /** @var $node SearchNode */
        $node = $event->getNode();

        foreach ($this->getRestrictions() as $fieldName => $fieldValue) {
            if (is_null($fieldValue)) {
                continue;
            }

            $this->logger->info('RESTRICTION onRqlSearch', ['field' => $fieldName, 'value' => $fieldValue]);

            $node->addSearchTerm($fieldName.':'.$fieldValue);
            $node->setVisited(false);
        }

        $event->setNode($node);
    }

    /**
     * gets the restrictions in an finalized array structure
     *
     * @return array restrictions
     */
    private function getRestrictions()
    {
        $restrictions = [];
        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($headerName, null);
            if (!is_null($headerValue) && $fieldSpec['type'] == 'int') {
                $headerValue = (int) $headerValue;
            }
            $restrictions[$fieldSpec['name']] = $headerValue;
        }
        return $restrictions;
    }

    /**
     * gets the current tentant that is saved on the entity. if it doesn't exist, return $checkValue
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
