<?php
/**
 * Created by PhpStorm.
 * User: samuel
 * Date: 17.08.15
 * Time: 14:12
 */

namespace Graviton\RestBundle\Tests\Model;

use lapistano\ProxyObject\ProxyBuilder;

class DocumentModelTest extends \PHPUnit_Framework_TestCase
{

    private $containerMock;

    private $documentModel;

    private $testRecord;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->containerMock = $this->getMockBuilder("\Symfony\Component\DependencyInjection\ContainerInterface")
            ->setMethods(["hasParameter", "getParameter"])
            ->getMockForAbstractClass();

        $this->testRecord = $this->getMockBuilder("\Graviton\RestBundle\Model\OriginRecordInterface")
            ->setMethods(["isOriginRecordModifiable", "getOriginRecord"])
            ->getMockForAbstractClass();

        $proxyBuilder = new ProxyBuilder("\Graviton\RestBundle\Model\DocumentModel");
        $this->documentModel = $proxyBuilder
            ->disableOriginalConstructor()
            ->setMethods(['checkIfOriginRecord'])
            ->setProperties(['container'])
            ->getProxy();
    }

    /**
     *
     * @dataProvider dataProvider
     * @return void
     */
    public function testCheckIfOriginRecord($hasContainerParam, $retContainerParam, $isModifiable, $originRecord)
    {
        $this->containerMock
            ->expects($this->once())
            ->method("hasParameter")
            ->with("graviton.not_modifiable.origin.records")
            ->willReturn($hasContainerParam);
        $this->containerMock
            ->expects($this->any())
            ->method("getParameter")
            ->with("graviton.not_modifiable.origin.records")
            ->willReturn($retContainerParam);

        $this->testRecord
            ->expects($this->any())
            ->method("isOriginRecordModifiable")
            ->willReturn($isModifiable);
        $this->testRecord
            ->expects($this->any())
            ->method("getOriginRecord")
            ->willReturn($originRecord);


        $this->documentModel->container = $this->containerMock;

        $this->documentModel->checkIfOriginRecord($this->testRecord);
    }
    /**
     *
     * @expectedException Graviton\ExceptionBundle\Exception\RestOriginRecordModifiedException
     * @return void
     */
    public function testCheckIfOriginRecordFailure()
    {
        $this->containerMock
            ->expects($this->once())
            ->method("hasParameter")
            ->with("graviton.not_modifiable.origin.records")
            ->willReturn(true);
        $this->containerMock
            ->expects($this->once())
            ->method("getParameter")
            ->with("graviton.not_modifiable.origin.records")
            ->willReturn(['core']);

        $this->testRecord
            ->expects($this->once())
            ->method("isOriginRecordModifiable")
            ->willReturn(false);
        $this->testRecord
            ->expects($this->once())
            ->method("getOriginRecord")
            ->willReturn('core');

        $this->documentModel->container = $this->containerMock;
        $this->documentModel->checkIfOriginRecord($this->testRecord);
    }

    public function dataProvider()
    {
        return array(
            array(false, array('core'), false, 'notCore'),
            array(true, array('core'), false, 'notCore'),
            array(true, array('core'), true, 'notCore'),
            array(true, array('core'), true, null),
            array(true, array('core', 'otherCore'), true, 'notCore')
        );
    }
}
