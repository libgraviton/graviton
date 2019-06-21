<?php
/**
 * ExtReferenceSearchListenerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Listener;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Listener\ExtReferenceSearchListener;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\ElemMatchNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Graviton\RqlParser\Node\Query\ArrayOperator\InNode;
use Graviton\RqlParser\Node\Query\ScalarOperator\EqNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceSearchListenerTest extends TestCase
{
    /**
     * @var ExtReferenceConverterInterface
     */
    private $converter;
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
     * setup type we want to test
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->converter = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceConverterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestAttrs = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->request = new Request();
        $this->request->attributes = $this->requestAttrs;

        $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();
        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);
    }

    /**
     * Create listener
     *
     * @param array $fields Field mapping
     * @return ExtReferenceSearchListener
     */
    private function createListener(array $fields)
    {
        return new ExtReferenceSearchListener($this->converter, $fields, $this->requestStack);
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with not query node
     *
     * @return void
     */
    public function testOnVisitNodeWithNotQueryNode()
    {
        $extrefMapping = [];

        $this->requestAttrs->expects($this->never())
            ->method('get');
        $this->converter->expects($this->never())
            ->method('getExtReference');

        $node = $this->getMockBuilder('Graviton\RqlParser\AbstractNode')
            ->disableOriginalConstructor()
            ->getMock();
        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with not mapped route
     *
     * @return void
     */
    public function testOnVisitNodeWithNotMappedRoute()
    {
        $this->expectException(\LogicException::class);

        $extrefMapping = [];

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->never())
            ->method('getExtReference');

        $node = $this->getMockBuilder('Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode')
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())
            ->method('getField')
            ->willReturn('field');
        $node->expects($this->never())
            ->method('getValue');

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with not mapped field
     *
     * @return void
     */
    public function testOnVisitNodeWithNotMappedField()
    {
        $extrefMapping = ['route.id' => []];

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->never())
            ->method('getExtReference');

        $node = $this->getMockBuilder('Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode')
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())
            ->method('getField')
            ->willReturn('field');
        $node->expects($this->never())
            ->method('getValue');

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with mapped scalar field
     *
     * @return void
     */
    public function testOnVisitNodeWithMappedScalarField()
    {
        $extrefMapping = ['route.id' => ['field']];
        $extrefUrl = 'extref.url';
        $extrefValue = ExtReference::create('Ref', 'id');
        $dbRefValue = \MongoDBRef::create($extrefValue->getRef(), $extrefValue->getId());

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willReturn($extrefValue);

        $node = new EqNode('field', $extrefUrl);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field', $dbRefValue),
            $event->getNode()
        );
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with mapped scalar field
     *
     * @return void
     */
    public function testOnVisitNodeWithMappedArrayField()
    {
        $extrefMapping = ['route.id' => ['field.0.$ref']];
        $extrefUrl = 'extref.url';
        $extrefValue = ExtReference::create('Ref', 'id');
        $dbRefValue = \MongoDBRef::create($extrefValue->getRef(), $extrefValue->getId());

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->exactly(2))
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willReturn($extrefValue);

        $node = new InNode('field..$ref', [$extrefUrl, $extrefUrl]);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new InNode('field..$ref', [$dbRefValue, $dbRefValue]),
            $event->getNode()
        );
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with mapped scalar field
     *
     * @return void
     */
    public function testOnVisitNodeWithInvalidExtref()
    {
        $extrefMapping = ['route.id' => ['field']];
        $extrefUrl = 'extref.url';
        $dbRefValue = \MongoDBRef::create(false, false);

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willThrowException(new \InvalidArgumentException());

        $node = new EqNode('field', $extrefUrl);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack());
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field', $dbRefValue),
            $event->getNode()
        );
    }


    /**
     * Test ExtReferenceSearchListener::onVisitNode() with context
     *
     * @return void
     */
    public function testOnVisitNodeWithContext()
    {
        $extrefMapping = ['route.id' => ['array.0.field.$ref']];
        $extrefUrl = 'extref.url';
        $extrefValue = ExtReference::create('Ref', 'id');
        $dbRefValue = \MongoDBRef::create($extrefValue->getRef(), $extrefValue->getId());

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willReturn($extrefValue);

        $node = new EqNode('field.$ref', $extrefUrl);
        $context = new \SplStack();
        $context->push(new ElemMatchNode('array', $node));


        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, $context);
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field.$ref', $dbRefValue),
            $event->getNode()
        );
    }
}
