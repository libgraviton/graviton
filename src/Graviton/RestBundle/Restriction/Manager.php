<?php
/**
 * restriction manager - returns rql query nodes from restrictions in the service definition
 */
namespace Graviton\RestBundle\Restriction;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Restriction\Handler\HandlerInterface;
use Graviton\RqlParser\Node\AbstractQueryNode;
use Graviton\RqlParser\Node\Query\LogicalOperator\AndNode;
use Graviton\RqlParser\Node\Query\ScalarOperator\EqNode;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class Manager
{

    /**
     * @var HandlerInterface[]
     */
    private static $handlers = [];

    /**
     * @var array
     */
    private $restrictedFieldMap;

    /**
     * Manager constructor.
     *
     * @param array $restrictedFieldMap map, which classes have which restrictions
     */
    public function __construct(array $restrictedFieldMap)
    {
        $this->restrictedFieldMap = $restrictedFieldMap;
    }

    /**
     * register a handler
     *
     * @param HandlerInterface $handler restriction handler
     *
     * @return void
     */
    public static function registerHandler(HandlerInterface $handler)
    {
        self::$handlers[$handler->getHandlerName()] = $handler;
    }

    /**
     * handle the request for a given entity
     *
     * @param DocumentRepository $repository repository
     *
     * @return bool|AndNode either false if no restrictions or an AndNode for filtering
     */
    public function handle(DocumentRepository $repository)
    {
        $className = $repository->getClassName();
        if (isset($this->restrictedFieldMap[$className]) && !empty($this->restrictedFieldMap[$className])) {
            $nodes = [];
            foreach ($this->restrictedFieldMap[$className] as $fieldPath => $handlers) {
                $nodes = array_merge(
                    $nodes,
                    $this->getHandlerValue($repository, $fieldPath, $handlers)
                );
            }
            return new AndNode($nodes);
        }
        return false;
    }

    /**
     * gets the value to restrict data. the handler can also return a AbstractQueryNode. not only a string
     *
     * @param DocumentRepository $repository the repository
     * @param string             $fieldPath  path to the field (can be complex; same syntax as in rql!)
     * @param array              $handlers   the specified handlers (array with handler names)
     *
     * @return array the rql nodes
     */
    private function getHandlerValue(DocumentRepository $repository, $fieldPath, array $handlers)
    {
        $nodes = [];
        foreach ($handlers as $handler) {
            if (!self::$handlers[$handler] instanceof HandlerInterface) {
                throw new \LogicException(
                    'Specified handler "'.$handler.'" is not registered with RestrictionManager!'
                );
            }
            $value = self::$handlers[$handler]->getValue($repository, $fieldPath);

            if (!$value instanceof AbstractQueryNode) {
                $value = new EqNode($fieldPath, $value);
            }

            $nodes[] = $value;
        }

        return $nodes;
    }
}
