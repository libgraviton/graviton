<?php
/**
 * RqlAllowedOperatorRequestListenerTest class file
 */

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\ExceptionBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\RestBundle\Event\RestEvent;
use Graviton\RestBundle\Listener\RqlAllowedOperatorRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Graviton\RqlParser\Node\LimitNode;
use Graviton\RqlParser\Node\Query\ScalarOperator\EqNode;
use Graviton\RqlParser\Node\SortNode;
use Graviton\RqlParser\Query;
use Graviton\RqlParser\QueryBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlAllowedOperatorRequestListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test RqlAllowedOperatorRequestListener::onKernelRequest() without RQL
     *
     * @return void
     */
    public function testOnKernelRequestWithoutRql()
    {
        $request = new Request();
        $request->attributes->set('rqlQuery', null);

        $event = new RestEvent();
        $event->setRequest($request);

        $listener = new RqlAllowedOperatorRequestListener();
        $listener->onKernelRequest($event);
    }

    /**
     * Test RqlAllowedOperatorRequestListener::onKernelRequest() with non-matched route
     *
     * @return void
     */
    public function testOnKernelRequestWithNonMatchedRoute()
    {
        $query = new Query();
        $query->setLimit(new LimitNode(1, 1));

        $request = new Request();
        $request->attributes->set('rqlQuery', $query);
        $request->attributes->set('_route', 'model.all');

        $event = new RestEvent();
        $event->setRequest($request);

        $listener = new RqlAllowedOperatorRequestListener();
        $listener->onKernelRequest($event);
    }

    /**
     * Test RqlAllowedOperatorRequestListener::onKernelRequest() with exception
     *
     * @param Query $query Query
     * @return void
     *
     * @dataProvider dataOnKernelRequestWithException
     */
    public function testOnKernelRequestWithException(Query $query)
    {
        $this->expectException(RqlOperatorNotAllowedException::class);

        $request = new Request();
        $request->attributes->set('rqlQuery', $query);
        $request->attributes->set('_route', 'model.get');

        $event = new RestEvent();
        $event->setRequest($request);

        $listener = new RqlAllowedOperatorRequestListener();
        $listener->onKernelRequest($event);
    }

    /**
     * Data for testOnKernelRequestWithException()
     *
     * @return array
     */
    public function dataOnKernelRequestWithException()
    {
        return [
            'limit' => [
                (new QueryBuilder())
                    ->addNode(new LimitNode(1, 2))
                    ->getQuery(),
            ],
            'sort' => [
                (new QueryBuilder())
                    ->addNode(new SortNode(['field' => SortNode::SORT_ASC]))
                    ->getQuery(),
            ],
            'query' => [
                (new QueryBuilder())
                    ->addNode(new EqNode('field', 1))
                    ->getQuery(),
            ],
        ];
    }
}
