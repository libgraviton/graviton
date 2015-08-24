<?php
/**
 * DocumentModelTest class
 */

namespace Graviton\RestBundle\Tests\Model;

use Graviton\RestBundle\Model\DocumentModel;
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
    /**
     * @var DocumentModel|object
     */
    private $sut;

    /**
     * @var RecordOriginInterface
     */
    private $testRecord;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->testRecord = $this->getMockBuilder("\Graviton\RestBundle\Model\RecordOriginInterface")
            ->setMethods(["isRecordOriginModifiable", "getRecordOrigin"])
            ->getMock();

        $proxyBuilder = new ProxyBuilder("\Graviton\RestBundle\Model\DocumentModel");
        $this->sut = $proxyBuilder
            ->disableOriginalConstructor()
            ->setProperties(array('notModifiableOriginRecords'))
            ->setMethods(['checkIfOriginRecord'])
            ->getProxy();
    }

    /**
     * CheckIfOriginRecord test
     *
     * @param array  $retContainerParam container parameter
     * @param bool   $isModifiable      can record be modified
     * @param string $recordOrigin      record origin
     *
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testCheckIfOriginRecord($retContainerParam, $isModifiable, $recordOrigin)
    {
        $this->testRecord
            ->expects($this->any())
            ->method("isRecordOriginModifiable")
            ->willReturn($isModifiable);
        $this->testRecord
            ->expects($this->any())
            ->method("getRecordOrigin")
            ->willReturn($recordOrigin);

        $this->sut->notModifiableOriginRecords = $retContainerParam;
        $this->sut->checkIfOriginRecord($this->testRecord);
    }

    /**
     * CheckIfOriginRecordFailure test
     *
     * @return void
     *
     * @expectedException Graviton\ExceptionBundle\Exception\RecordOriginModifiedException
     */
    public function testCheckIfOriginRecordFailure()
    {
        $this->testRecord
            ->expects($this->once())
            ->method("isRecordOriginModifiable")
            ->willReturn(false);
        $this->testRecord
            ->expects($this->once())
            ->method("getRecordOrigin")
            ->willReturn('core');

        $this->sut->notModifiableOriginRecords = array('core');
        $this->sut->checkIfOriginRecord($this->testRecord);
    }

    /**
     * supplies different data
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array(array('core'), false, 'notCore'),
            array(array('core'), false, 'notCore'),
            array(array('core'), true, 'notCore'),
            array(array('core'), true, null),
            array(array('core', 'otherCore'), true, 'notCore')
        );
    }
}
