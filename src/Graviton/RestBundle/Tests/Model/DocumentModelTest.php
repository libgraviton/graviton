<?php
/**
 * DocumentModelTest class
 */

namespace Graviton\RestBundle\Tests\Model;

use lapistano\ProxyObject\ProxyBuilder;

/**
 * DocumentModel test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
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

        $this->testRecord = $this->getMockBuilder("\Graviton\RestBundle\Model\RecordOriginInterface")
            ->setMethods(["isRecordOriginModifiable", "getRecordOrigin"])
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
            ->method("isRecordOriginModifiable")
            ->willReturn($isModifiable);
        $this->testRecord
            ->expects($this->any())
            ->method("getRecordOrigin")
            ->willReturn($originRecord);


        $this->documentModel->container = $this->containerMock;

        $this->documentModel->checkIfOriginRecord($this->testRecord);
    }

    /**
     *
     * @expectedException Graviton\ExceptionBundle\Exception\RecordOriginModifiedException
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
            ->method("isRecordOriginModifiable")
            ->willReturn(false);
        $this->testRecord
            ->expects($this->once())
            ->method("getRecordOrigin")
            ->willReturn('core');

        $this->documentModel->container = $this->containerMock;
        $this->documentModel->checkIfOriginRecord($this->testRecord);
    }

    /**
     * supplies different data
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array(false, array('core'), false, 'notCore'),
            array(true, array('core'), false, 'notCore'),
            array(true, array('core'), true, 'notCore'),
            array(true, array('core'), true, null),
            array(true, array('core', 'otherCore'), true, 'notCore'),
        );
    }
}
