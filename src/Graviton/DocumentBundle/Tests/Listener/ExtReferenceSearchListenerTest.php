<?php
/**
 * ExtReferenceSearchListenerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Listener;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Listener\ExtReferenceSearchListener;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Graviton\Rql\Event\VisitNodeEvent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Xiag\Rql\Parser\Node\Query\ArrayOperator\InNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceSearchListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtReferenceConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestAttrs;
    /**
     * @var RequestStack|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestStack;


    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
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

        $node = $this->getMockBuilder('Xiag\Rql\Parser\AbstractNode')
            ->disableOriginalConstructor()
            ->getMock();
        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder);
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with not mapped route
     *
     * @return void
     * @expectedException \LogicException
     */
    public function testOnVisitNodeWithNotMappedRoute()
    {
        $extrefMapping = [];

        $this->requestAttrs->expects($this->once())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter->expects($this->never())
            ->method('getExtReference');

        $node = $this->getMockBuilder('Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode')
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

        $event = new VisitNodeEvent($node, $builder);
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

        $node = $this->getMockBuilder('Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode')
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

        $event = new VisitNodeEvent($node, $builder);
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
        $extrefMapping = ['route.id' => ['originalField' => 'exposedField']];
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

        $node = new EqNode('exposedField', $extrefUrl);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder);
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('originalField', $dbRefValue),
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
        $extrefMapping = ['route.id' => ['originalField.ref' => 'exposedField.0.$ref']];
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

        $node = new InNode('exposedField..$ref', [$extrefUrl, $extrefUrl]);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder);
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new InNode('originalField.ref', [$dbRefValue, $dbRefValue]),
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
        $extrefMapping = ['route.id' => ['originalField' => 'exposedField']];
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

        $node = new EqNode('exposedField', $extrefUrl);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder);
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('originalField', $dbRefValue),
            $event->getNode()
        );
    }
}
