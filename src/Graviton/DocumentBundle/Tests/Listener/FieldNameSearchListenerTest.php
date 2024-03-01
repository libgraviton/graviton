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
     * Create listener
     *
     * @param array $fields Field mapping
     * @return FieldNameSearchListener
     */
    private function createListener(array $fields)
    {
        return new FieldNameSearchListener($fields);
    }

    /**
     * Test FieldNameSearchListener::onVisitNode() with not query node
     *
     * @return void
     */
    public function testOnVisitNodeWithNotQueryNode()
    {
        $fieldMapping = [];

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
        $mapping = ['Hans\Class' => []];

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

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Class');
        $listener = $this->createListener($mapping);
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
        $mapping = ['Hans\Class' => ['field' => '$field']];
        $fieldValue = 'field-value';

        $node = new EqNode('$field', $fieldValue);

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Class');
        $listener = $this->createListener($mapping);
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
        $mapping = [
            'Hans\Class' => [
                'array'             => '$array',
                'array.0'           => '$array.0',
                'array.0.field'     => '$array.0.$field',
                'array.0.field.ref' => '$array.0.$field.$ref',
            ],
        ];
        $fieldValue = 'field-value';

        $node = new EqNode('$field.$ref', $fieldValue);
        $context = new \SplStack();
        $context->push(new ElemMatchNode('$array', $node));


        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, $context, false, 'Hans\Class');
        $listener = $this->createListener($mapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field.ref', $fieldValue),
            $event->getNode()
        );
    }
}
