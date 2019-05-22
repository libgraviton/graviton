<?php
/**
 * model event listener that restricts data access based on http headers
 */

namespace Graviton\RestBundle\Listener;

use Doctrine\Common\Collections\Criteria;
use Graviton\AnalyticsBundle\Event\PreAggregateEvent;
use Graviton\CoreBundle\Util\CoreUtils;
use Graviton\ExceptionBundle\Exception\RestrictedIdCollisionException;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\Rql\Event\VisitNodeEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionListener
{

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
     * @param array        $dataRestrictionMap data restriction configuration
     * @param RequestStack $requestStack       request stack
     */
    public function __construct(?array $dataRestrictionMap, RequestStack $requestStack)
    {
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

            $builder->addAnd(
                $builder->expr()->field($fieldSpec['name'])->in([null, $headerValue])
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
            // skip the id collision check if no id..
            if (!is_null($entityId)) {
                $this->checkIdCollision($event, $entityId, $fieldName, $fieldValue);
            }

            if (is_null($fieldValue)) {
                continue;
            }

            $entity[$fieldName] = $fieldValue;
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

        $event->setPipeline($newPipeline);

        return $event;
    }

    public function onSolrSearch(VisitNodeEvent $event)
    {
        var_dump($event); die;
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
     * checks for an id collision. that is, if we try to insert/delete a record that
     * already exist with ANOTHER $checkField value of $checkValue that we currently trying
     * to insert/delete. basically this is the case if one tenant group tries to modify or delete
     * the record of another one
     *
     * @param EntityPrePersistEvent $event      event
     * @param mixed                 $entityId   entity id
     * @param string                $checkField field to check
     * @param mixed                 $checkValue value to check for on the field
     *
     * @throws RestrictedIdCollisionException
     *
     * @return void
     */
    private function checkIdCollision(EntityPrePersistEvent $event, $entityId, $checkField, $checkValue)
    {
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->eq('id', $entityId));
        $criteria->andWhere(Criteria::expr()->neq($checkField, $checkValue));

        if (!$event->getRepository()->matching($criteria)->isEmpty()) {
            throw new RestrictedIdCollisionException();
        }
    }
}
