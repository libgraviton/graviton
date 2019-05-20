<?php
/**
 * model event listener that restricts data access based on http headers
 */

namespace Graviton\RestBundle\Listener;

use Graviton\CoreBundle\Util\CoreUtils;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\RestBundle\Event\RestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

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
                $headerValue = (int)$headerValue;
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
     * @param EntityPrePersistEvent $event
     *
     * @return EntityPrePersistEvent event
     */
    public function onEntityPrePersist(EntityPrePersistEvent $event)
    {
        if (!is_array($this->dataRestrictionMap) ||
            empty($this->dataRestrictionMap) ||
            !($event->getEntity() instanceof \ArrayAccess)
        ) {
            return;
        }

        $entity = $event->getEntity();
        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($headerName, null);
            if (!is_null($headerValue)) {
                $entity[$fieldSpec['name']] = $headerValue;
            }
        }

        $event->setEntity($entity);

        return $event;
    }
}
