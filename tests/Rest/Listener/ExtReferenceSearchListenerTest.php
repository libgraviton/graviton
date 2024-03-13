<?php
/**
 * ExtReferenceSearchListenerTest class file
 */

namespace Graviton\Tests\Rest\Listener;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Listener\ExtReferenceSearchListener;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Model\RuntimeDefinition;
use Graviton\RestBundle\Service\RestServiceLocator;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\ElemMatchNode;
use Graviton\RqlParser\Node\Query\ArrayOperator\InNode;
use Graviton\RqlParser\Node\Query\ScalarOperator\EqNode;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceSearchListenerTest extends TestCase
{
    /**
     * @var ExtReferenceConverter
     */
    private $converter;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->converter = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceConverter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create listener
     *
     * @param array $fields Field mapping
     * @return ExtReferenceSearchListener
     */
    private function createListener(array $fields) : ExtReferenceSearchListener
    {
        $runtimeDef = new RuntimeDefinition();
        $runtimeDef->setExtRefFields($fields);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('has')->willReturn(true);

        $model = $this->createStub(DocumentModel::class);
        $model->method('getRuntimeDefinition')->willReturn($runtimeDef);



        $locator = $this->createStub(RestServiceLocator::class);
        $locator->method('getDocumentModel')->willReturn($model);

        return new ExtReferenceSearchListener($this->converter, $locator);
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with not query node
     *
     * @return void
     */
    public function testOnVisitNodeWithNotQueryNode()
    {
        $extrefMapping = [];

        $this->converter->expects($this->never())
            ->method('getExtReference');

        $node = $this->getMockBuilder('Graviton\RqlParser\AbstractNode')
            ->disableOriginalConstructor()
            ->getMock();
        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Document\Class');
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());
    }

    /**
     * Test ExtReferenceSearchListener::onVisitNode() with not mapped field
     *
     * @return void
     */
    public function testOnVisitNodeWithNotMappedField()
    {
        $extrefMapping = [];

        $this->converter->expects($this->never())
            ->method('getExtReference');

        $node = $this->getMockBuilder('Graviton\RqlParser\Node\Query\AbstractScalarOperatorNode')
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->never())
            ->method('getField')
            ->willReturn('field');
        $node->expects($this->never())
            ->method('getValue');

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Document\Class');
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
        $extrefMapping = ['field'];
        $extrefUrl = 'extref.url';
        $extrefValue = ExtReference::create('Ref', 'id');
        $dbRefValue = $extrefValue->jsonSerialize();

        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willReturn($extrefValue);

        $node = new EqNode('field', $extrefUrl);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Document\Class');
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
        $extrefMapping = ['field.0.$ref'];
        $extrefUrl = 'extref.url';
        $extrefValue = ExtReference::create('Ref', 'id');
        $dbRefValue = $extrefValue->jsonSerialize();

        $this->converter->expects($this->exactly(2))
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willReturn($extrefValue);

        $node = new InNode('field..$ref', [$extrefUrl, $extrefUrl]);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Document\Class');
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
        $extrefMapping = ['field'];
        $extrefUrl = 'extref.url';

        $this->converter->expects($this->once())
            ->method('getExtReference')
            ->with($extrefUrl)
            ->willThrowException(new \InvalidArgumentException());

        $node = new EqNode('field', $extrefUrl);

        $builder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new VisitNodeEvent($node, $builder, new \SplStack(), false, 'Hans\Document\Class');
        $listener = $this->createListener($extrefMapping);
        $listener->onVisitNode($event);

        $this->assertNotSame($node, $event->getNode());
        $this->assertSame($builder, $event->getBuilder());

        $this->assertEquals(
            new EqNode('field', []),
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
        $extrefMapping = ['array.0.field.$ref'];
        $extrefUrl = 'extref.url';
        $extrefValue = ExtReference::create('Ref', 'id');
        $dbRefValue = $extrefValue->jsonSerialize();

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

        $event = new VisitNodeEvent($node, $builder, $context, false, 'Hans\Document\Class');
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
