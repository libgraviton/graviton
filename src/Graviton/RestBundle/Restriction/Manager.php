<?php
/**
 * restriction manager - returns rql query nodes from restrictions in the service definition
 */
namespace Graviton\RestBundle\Restriction;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Restriction\Handler\GlobalHandlerAbstract;
use Graviton\RestBundle\Restriction\Handler\HandlerInterface;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class Manager
{

    public const EVENT_READ = 'read';
    public const EVENT_INSERT = 'insert';

    /**
     * @var HandlerInterface[]
     */
    private static $handlers = [];

    /**
     * @var HandlerInterface[]
     */
    private static $globalHandlers = [];

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
     * register a global handler - one that is always called
     *
     * @param HandlerInterface $handler restriction handler
     *
     * @return void
     */
    public static function registerGlobalHandler(HandlerInterface $handler)
    {
        self::$globalHandlers[$handler->getHandlerName()] = $handler;
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

        $nodes = [];
        if (isset($this->restrictedFieldMap[$className]) && !empty($this->restrictedFieldMap[$className])) {
            foreach ($this->restrictedFieldMap[$className] as $fieldPath => $handlers) {
                $nodes = array_merge(
                    $nodes,
                    $this->getHandlerValue($repository, $fieldPath, $handlers)
                );
            }
        }

        // global handlers
        foreach (self::$globalHandlers as $handler) {
            $handlerNode = $handler->getValue($repository, '');
            if ($handlerNode instanceof AbstractQueryNode) {
                $nodes[] = $handlerNode;
            }
        }

        if (!empty($nodes)) {
            return new AndNode($nodes);
        }

        return false;
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
        foreach (self::$globalHandlers as $handler) {
            if ($handler instanceof GlobalHandlerAbstract) {
                $entity = $handler->restrictInsert($entity);
            }
        }
        return $entity;
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
