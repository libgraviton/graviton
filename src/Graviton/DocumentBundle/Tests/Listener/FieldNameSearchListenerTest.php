<?php
/**
 * FieldNameSearchListenerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Listener;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\DocumentBundle\Listener\FieldNameSearchListener;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\ElemMatchNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Graviton\RqlParser\AbstractNode;
use Graviton\RqlParser\Node\Query\ScalarOperator\EqNode;
use Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldNameSearchListenerTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ParameterBag
     */
    private $requestAttrs;
    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * Setup the test
     *
     * @return void
     */
    protected function setUp() : void
    {
        $this->requestAttrs = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->request = new Request();
        $this->request->attributes = $this->requestAttrs;

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();
        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        parent::setUp();
    }

    /**
     * Create listener
     *
     * @param array $fields Field mapping
     * @return FieldNameSearchListener
     */
    private function createListener(array $fields)
    {
        return new FieldNameSearchListener($fields, $this->requestStack);
    }

    /**
     * Test FieldNameSearchListener::onVisitNode() with not query node
     *
     * @return void
     */
    public function testOnVisitNodeWithNotQueryNode()
    {
        $fieldMapping = [];

        $this->requestAttrs->expects($this->never())
            ->method('get');

        $node = $this->getMockBuilder(AbstractNode::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($fieldMapping);
        $listener->onVisitNode($event);

        $this->assertSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());
    }

    /**
     * Test FieldNameSearchListener::onVisitNode() with not mapped route
     *
     * @return void
     */
    public function testOnVisitNodeWithNotMappedRoute()
    {
        $fieldMapping = [];
        $this->expectException(\LogicException::class);

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');

        $node = $this->getMockBuilder(AbstractScalarOperatorNode::class)
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())
            ->method('getField')
            ->willReturn('field');
        $node->expects($this->never())
            ->method('getValue');

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($fieldMapping);
        $listener->onVisitNode($event);
    }

    /**
     * Test FieldNameSearchListener::onVisitNode() with not mapped field
     *
     * @return void
     */
    public function testOnVisitNodeWithNotMappedField()
    {
        $fieldMapping = ['route.id' => []];

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');

        $node = $this->getMockBuilder(AbstractScalarOperatorNode::class)
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())
            ->method('getField')
            ->willReturn('field');
        $node->expects($this->never())
            ->method('getValue');

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($fieldMapping);
        $listener->onVisitNode($event);

        $this->assertSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() simple
     *
     * @return void
     */
    public function testOnVisitNodeSimple()
    {
        $fieldMapping = ['route.id' => ['field' => '$field']];
        $fieldValue = 'field-value';

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');

        $node = new EqNode('$field', $fieldValue);

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($fieldMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field', $fieldValue),
            $event->getNode()
        );
    }

    /**
     * Test FieldNameSearchListener::onVisitNode() with context
     *
     * @return void
     */
    public function testOnVisitNodeWithContext()
    {
        $fieldMapping = [
            'route.id' => [
                'array'             => '$array',
                'array.0'           => '$array.0',
                'array.0.field'     => '$array.0.$field',
                'array.0.field.ref' => '$array.0.$field.$ref',
            ],
        ];
        $fieldValue = 'field-value';

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');

        $node = new EqNode('$field.$ref', $fieldValue);
        $context = new \SplStack();
        $context->push(new ElemMatchNode('$array', $node));


        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, $context);
        $listener = $this->createListener($fieldMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field.ref', $fieldValue),
            $event->getNode()
        );
    }
}
