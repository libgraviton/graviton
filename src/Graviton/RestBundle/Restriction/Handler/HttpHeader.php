<?php
/**
 * http header based global restriction handler
 */
namespace Graviton\RestBundle\Restriction\Handler;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\CoreBundle\Util\CoreUtils;
use Graviton\RestBundle\Restriction\Manager;
use Symfony\Component\HttpFoundation\RequestStack;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class HttpHeader extends GlobalHandlerAbstract implements HandlerInterface
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
     * returns the name of this handler, string based id. this is referenced in the service definition.
     *
     * @return string handler name
     */
    public function getHandlerName()
    {
        return 'httpheader';
    }

    /**
     * returns the events this restriction handler can handle
     *
     * @return array array of event names
     */
    public function getEvents(): array
    {
        return [
            Manager::EVENT_INSERT,
            Manager::EVENT_READ
        ];
    }

    /**
     * modifies stuff on insert
     *
     * @param \ArrayAccess $entity entity
     *
     * @return \ArrayAccess entity
     */
    public function restrictInsert(\ArrayAccess $entity)
    {
        if (!is_array($this->dataRestrictionMap) ||
            empty($this->dataRestrictionMap)
        ) {
            return $entity;
        }

        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($headerName, null);
            if (!is_null($headerValue)) {
                $entity[$fieldSpec['name']] = $headerValue;
            }
        }

        return $entity;
    }

    /**
     * gets the actual value or an AbstractQueryNode that is used to filter the data.
     *
     * @param DocumentRepository $repository the repository
     * @param string             $fieldPath  field path
     *
     * @return string|AbstractQueryNode string for eq() filtering or a AbstractQueryNode instance
     */
    public function getValue(DocumentRepository $repository, $fieldPath)
    {
        if (!is_array($this->dataRestrictionMap) || empty($this->dataRestrictionMap)) {
            return null;
        }

        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($headerName, null);

            if ($headerValue == null) {
                continue;
            }

            if ($fieldSpec['type'] == 'int') {
                $headerValue = (int) $headerValue;
            }

            return new OrNode(
                [
                    new EqNode($fieldSpec['name'], $headerValue),
                    new EqNode($fieldSpec['name'], null)
                ]
            );
        }
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
}
